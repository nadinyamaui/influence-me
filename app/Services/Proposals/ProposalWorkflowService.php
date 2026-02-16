<?php

namespace App\Services\Proposals;

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRevisionRequested;
use App\Mail\ProposalSent;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ProposalWorkflowService
{
    public function send(User $user, Proposal $proposal): Proposal
    {
        $this->ensureOwner($user, $proposal);
        $this->assertSendable($proposal);

        return DB::transaction(function () use ($proposal): Proposal {
            $proposal->update([
                'status' => ProposalStatus::Sent,
                'sent_at' => now(),
                'responded_at' => null,
            ]);

            $proposal->loadMissing(['user', 'client.clientUser']);

            Mail::to($proposal->client->email)->send(new ProposalSent($proposal));

            return $proposal->refresh();
        });
    }

    public function approve(ClientUser $clientUser, Proposal $proposal): Proposal
    {
        return DB::transaction(function () use ($clientUser, $proposal): Proposal {
            $lockedProposal = $this->lockProposalForResponse($proposal);

            $this->ensureClientScope($clientUser, $lockedProposal);
            $this->assertRespondable($lockedProposal);

            $lockedProposal->update([
                'status' => ProposalStatus::Approved,
                'responded_at' => now(),
                'revision_notes' => null,
            ]);

            $lockedProposal->loadMissing(['user', 'client']);

            if (filled($lockedProposal->user?->email)) {
                Mail::to($lockedProposal->user->email)->send(new ProposalApproved($lockedProposal));
            }

            return $lockedProposal->refresh();
        });
    }

    public function requestChanges(ClientUser $clientUser, Proposal $proposal, string $revisionNotes): Proposal
    {
        $notes = trim($revisionNotes);

        if (mb_strlen($notes) < 10) {
            throw ValidationException::withMessages([
                'revisionNotes' => 'Revision notes must be at least 10 characters.',
            ]);
        }

        return DB::transaction(function () use ($clientUser, $proposal, $notes): Proposal {
            $lockedProposal = $this->lockProposalForResponse($proposal);

            $this->ensureClientScope($clientUser, $lockedProposal);
            $this->assertRespondable($lockedProposal);

            $lockedProposal->update([
                'status' => ProposalStatus::Revised,
                'revision_notes' => $notes,
                'responded_at' => now(),
            ]);

            $lockedProposal->loadMissing(['user', 'client']);

            if (filled($lockedProposal->user?->email)) {
                Mail::to($lockedProposal->user->email)->send(new ProposalRevisionRequested($lockedProposal));
            }

            return $lockedProposal->refresh();
        });
    }

    public function assertSendable(Proposal $proposal): void
    {
        if (! in_array($proposal->status, [ProposalStatus::Draft, ProposalStatus::Revised], true)) {
            throw ValidationException::withMessages([
                'send' => 'Only draft or revised proposals can be sent.',
            ]);
        }

        $proposal->loadMissing('client', 'campaigns:id,name,proposal_id');

        if (blank($proposal->client?->email)) {
            throw ValidationException::withMessages([
                'send' => 'Add a client email before sending this proposal.',
            ]);
        }

        if ($proposal->campaigns->isEmpty()) {
            throw ValidationException::withMessages([
                'send' => 'Link at least one campaign before sending this proposal.',
            ]);
        }

        $campaignIds = $proposal->campaigns->pluck('id')->all();

        $campaignsWithoutScheduledContent = Campaign::query()
            ->whereIn('id', $campaignIds)
            ->whereDoesntHave('scheduledPosts')
            ->pluck('name')
            ->all();

        if ($campaignsWithoutScheduledContent !== []) {
            throw ValidationException::withMessages([
                'send' => 'Every linked campaign must include at least one scheduled content item.',
            ]);
        }

        $hasInvalidScopeEntries = ScheduledPost::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where(function ($query) use ($proposal): void {
                $query->where('user_id', '!=', $proposal->user_id)
                    ->orWhere('client_id', '!=', $proposal->client_id)
                    ->orWhereNull('client_id');
            })
            ->exists();

        if ($hasInvalidScopeEntries) {
            throw ValidationException::withMessages([
                'send' => 'Scheduled content must belong to the same influencer and client as this proposal.',
            ]);
        }
    }

    public function createDraft(User $user, array $payload): Proposal
    {
        return $user->proposals()->create([
            'client_id' => (int) $payload['client_id'],
            'title' => $payload['title'],
            'content' => '',
            'status' => ProposalStatus::Draft,
        ]);
    }

    public function updateDraftWithCampaignSchedule(User $user, Proposal $proposal, array $payload): Proposal
    {
        $this->ensureOwner($user, $proposal);
        $this->ensureEditable($proposal);

        return DB::transaction(function () use ($proposal, $user, $payload): Proposal {
            $clientId = $this->resolveDraftClientId($user, $proposal, $payload);
            $campaignPayloads = $this->normalizeDraftCampaignPayloads($payload['campaigns'] ?? []);

            $proposal->update([
                'client_id' => $clientId,
                'title' => is_string($payload['title'] ?? null) ? $payload['title'] : $proposal->title,
                'content' => is_string($payload['content'] ?? null) ? $payload['content'] : $proposal->content,
            ]);

            $this->detachRemovedCampaigns($proposal, $campaignPayloads);
            $this->syncCampaignsAndScheduledItems($proposal, $user, $campaignPayloads, true);

            return $proposal->refresh();
        });
    }

    private function syncCampaignsAndScheduledItems(Proposal $proposal, User $user, array $campaignPayloads, bool $pruneMissingItems): void
    {
        foreach ($campaignPayloads as $campaignPayload) {
            $campaign = $this->resolveOrCreateCampaign($proposal, $user, $campaignPayload);

            if ($campaign === null) {
                continue;
            }

            $scheduledItemIds = [];
            $canPruneItems = true;

            foreach ($campaignPayload['scheduled_items'] ?? [] as $scheduledItemPayload) {
                $scheduledAttributes = $this->resolveScheduledPostAttributes($proposal, $user, $scheduledItemPayload);

                if ($scheduledAttributes === null) {
                    $canPruneItems = false;

                    continue;
                }

                $scheduledItemId = $scheduledItemPayload['id'] ?? null;

                if ($scheduledItemId !== null) {
                    $scheduledPost = $campaign->scheduledPosts()
                        ->where('user_id', $user->id)
                        ->whereKey((int) $scheduledItemId)
                        ->first();

                    if ($scheduledPost === null) {
                        $canPruneItems = false;

                        continue;
                    }

                    $scheduledPost->update($scheduledAttributes);
                    $scheduledItemIds[] = $scheduledPost->id;

                    continue;
                }

                $scheduledPost = $campaign->scheduledPosts()->create($scheduledAttributes);
                $scheduledItemIds[] = $scheduledPost->id;
            }

            if ($pruneMissingItems && $canPruneItems) {
                $campaign->scheduledPosts()
                    ->where('user_id', $user->id)
                    ->when(
                        $scheduledItemIds !== [],
                        fn ($query) => $query->whereNotIn('id', $scheduledItemIds),
                        fn ($query) => $query,
                    )
                    ->delete();
            }
        }
    }

    private function resolveOrCreateCampaign(Proposal $proposal, User $user, array $campaignPayload): ?Campaign
    {
        $campaignId = $campaignPayload['id'] ?? null;
        $campaignName = is_string($campaignPayload['name'] ?? null) ? trim($campaignPayload['name']) : '';

        if ($campaignId === null) {
            if ($campaignName === '') {
                return null;
            }

            $existingCampaign = Campaign::query()
                ->where('client_id', $proposal->client_id)
                ->where('name', $campaignName)
                ->first();

            if ($existingCampaign !== null) {
                if ($existingCampaign->proposal_id !== null && $existingCampaign->proposal_id !== $proposal->id) {
                    return null;
                }

                $existingCampaign->update([
                    'proposal_id' => $proposal->id,
                    'description' => $campaignPayload['description'] ?? $existingCampaign->description,
                ]);

                return $existingCampaign;
            }

            return Campaign::query()->create([
                'client_id' => $proposal->client_id,
                'proposal_id' => $proposal->id,
                'name' => $campaignName,
                'description' => $campaignPayload['description'] ?? null,
            ]);
        }

        $campaign = Campaign::query()
            ->whereKey((int) $campaignId)
            ->where('client_id', $proposal->client_id)
            ->where('proposal_id', $proposal->id)
            ->whereHas('client', fn ($query) => $query->where('user_id', $user->id))
            ->first();

        if ($campaign === null) {
            return null;
        }

        $campaign->update([
            'name' => $campaignName !== '' ? $campaignName : $campaign->name,
            'description' => $campaignPayload['description'] ?? $campaign->description,
        ]);

        return $campaign;
    }

    private function detachRemovedCampaigns(Proposal $proposal, array $campaignPayloads): void
    {
        $campaignIds = collect($campaignPayloads)
            ->pluck('id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($campaignIds === []) {
            return;
        }

        $proposal->campaigns()
            ->whereNotIn('id', $campaignIds)
            ->update(['proposal_id' => null]);
    }

    private function ensureOwner(User $user, Proposal $proposal): void
    {
        if ($proposal->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to modify this proposal.');
        }
    }

    private function ensureClientScope(ClientUser $clientUser, Proposal $proposal): void
    {
        if ($proposal->client_id !== $clientUser->client_id) {
            throw new AuthorizationException('You are not authorized to modify this proposal.');
        }
    }

    private function assertRespondable(Proposal $proposal): void
    {
        if ($proposal->status !== ProposalStatus::Sent || $proposal->responded_at !== null) {
            throw ValidationException::withMessages([
                'proposal' => 'Only sent proposals awaiting response can be updated.',
            ]);
        }
    }

    private function lockProposalForResponse(Proposal $proposal): Proposal
    {
        return Proposal::query()
            ->whereKey($proposal->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureEditable(Proposal $proposal): void
    {
        if (! in_array($proposal->status, [ProposalStatus::Draft, ProposalStatus::Revised], true)) {
            throw ValidationException::withMessages([
                'proposal' => 'Only draft or revised proposals can be edited.',
            ]);
        }
    }

    private function resolveDraftClientId(User $user, Proposal $proposal, array $payload): int
    {
        $clientId = $payload['client_id'] ?? null;

        if (! is_numeric($clientId)) {
            return $proposal->client_id;
        }

        $ownedClientExists = Client::query()
            ->where('user_id', $user->id)
            ->whereKey((int) $clientId)
            ->exists();

        return $ownedClientExists ? (int) $clientId : $proposal->client_id;
    }

    private function normalizeDraftCampaignPayloads(array $campaignPayloads): array
    {
        return collect($campaignPayloads)
            ->values()
            ->map(fn (array $campaignPayload): array => [
                'id' => filled($campaignPayload['id'] ?? null) ? (int) $campaignPayload['id'] : null,
                'name' => is_string($campaignPayload['name'] ?? null) ? $campaignPayload['name'] : '',
                'description' => is_string($campaignPayload['description'] ?? null) ? $campaignPayload['description'] : '',
                'scheduled_items' => is_array($campaignPayload['scheduled_items'] ?? null) ? $campaignPayload['scheduled_items'] : [],
            ])
            ->all();
    }

    private function resolveScheduledPostAttributes(Proposal $proposal, User $user, array $scheduledItemPayload): ?array
    {
        $instagramAccountId = $this->resolveOwnedInstagramAccountId($user, $scheduledItemPayload['instagram_account_id'] ?? null);
        $scheduledAt = $this->resolveScheduledAt($scheduledItemPayload['scheduled_at'] ?? null);
        $mediaType = $this->resolveMediaType($scheduledItemPayload['media_type'] ?? null);
        $title = is_string($scheduledItemPayload['title'] ?? null) ? trim($scheduledItemPayload['title']) : '';

        if ($instagramAccountId === null || $scheduledAt === null || $mediaType === null || $title === '') {
            return null;
        }

        return [
            'user_id' => $user->id,
            'client_id' => $proposal->client_id,
            'instagram_account_id' => $instagramAccountId,
            'title' => $title,
            'description' => is_string($scheduledItemPayload['description'] ?? null) ? $scheduledItemPayload['description'] : null,
            'media_type' => $mediaType,
            'scheduled_at' => $scheduledAt,
            'status' => ScheduledPostStatus::Planned,
        ];
    }

    private function resolveOwnedInstagramAccountId(User $user, mixed $instagramAccountId): ?int
    {
        if (! is_numeric($instagramAccountId)) {
            return null;
        }

        $accountId = (int) $instagramAccountId;

        $ownedAccountExists = InstagramAccount::query()
            ->where('user_id', $user->id)
            ->whereKey($accountId)
            ->exists();

        return $ownedAccountExists ? $accountId : null;
    }

    private function resolveScheduledAt(mixed $scheduledAt): ?Carbon
    {
        if (! is_string($scheduledAt) || trim($scheduledAt) === '') {
            return null;
        }

        try {
            return Carbon::parse($scheduledAt);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveMediaType(mixed $mediaType): ?string
    {
        if (! is_string($mediaType)) {
            return null;
        }

        $mediaTypeValues = collect(MediaType::cases())
            ->map(fn (MediaType $item): string => $item->value)
            ->all();

        return in_array($mediaType, $mediaTypeValues, true) ? $mediaType : null;
    }
}
