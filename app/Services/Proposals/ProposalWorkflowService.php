<?php

namespace App\Services\Proposals;

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProposalWorkflowService
{
    public function createDraft(User $user, array $payload): Proposal
    {
        return $user->proposals()->create([
            'client_id' => (int) $payload['client_id'],
            'title' => $payload['title'],
            'content' => '',
            'status' => ProposalStatus::Draft,
        ]);
    }

    public function createDraftWithCampaignSchedule(User $user, array $payload): Proposal
    {
        return DB::transaction(function () use ($user, $payload): Proposal {
            $proposal = $user->proposals()->create([
                'client_id' => (int) $payload['client_id'],
                'title' => $payload['title'],
                'content' => $payload['content'],
                'status' => ProposalStatus::Draft,
            ]);

            $this->syncCampaignsAndScheduledItems($proposal, $user, $payload['campaigns'], false);

            return $proposal;
        });
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

    public function duplicate(User $user, Proposal $proposal): Proposal
    {
        $this->ensureOwner($user, $proposal);

        return DB::transaction(function () use ($user, $proposal): Proposal {
            $duplicate = $user->proposals()->create([
                'client_id' => $proposal->client_id,
                'title' => $proposal->title.' (Copy)',
                'content' => $proposal->content,
                'status' => ProposalStatus::Draft,
                'revision_notes' => null,
                'sent_at' => null,
                'responded_at' => null,
            ]);

            $proposal->loadMissing('campaigns.scheduledPosts');

            foreach ($proposal->campaigns as $campaign) {
                $copiedCampaign = $campaign->replicate(['proposal_id']);
                $copiedCampaign->proposal_id = $duplicate->id;
                $copiedCampaign->save();

                foreach ($campaign->scheduledPosts as $scheduledPost) {
                    $copiedScheduledPost = $scheduledPost->replicate(['campaign_id', 'status']);
                    $copiedScheduledPost->campaign_id = $copiedCampaign->id;
                    $copiedScheduledPost->status = ScheduledPostStatus::Planned;
                    $copiedScheduledPost->save();
                }
            }

            return $duplicate;
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
