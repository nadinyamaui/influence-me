<?php

namespace App\Services\Proposals;

use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Models\Campaign;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
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
            $proposal->update([
                'client_id' => (int) $payload['client_id'],
                'title' => $payload['title'],
                'content' => $payload['content'],
            ]);

            $this->detachRemovedCampaigns($proposal, $payload['campaigns']);
            $this->syncCampaignsAndScheduledItems($proposal, $user, $payload['campaigns'], true);

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
            $scheduledItemIds = [];

            foreach ($campaignPayload['scheduled_items'] as $scheduledItemPayload) {
                $scheduledItemId = $scheduledItemPayload['id'] ?? null;
                $attributes = [
                    'user_id' => $user->id,
                    'client_id' => $proposal->client_id,
                    'instagram_account_id' => (int) $scheduledItemPayload['instagram_account_id'],
                    'title' => $scheduledItemPayload['title'],
                    'description' => $scheduledItemPayload['description'] ?? null,
                    'media_type' => $scheduledItemPayload['media_type'],
                    'scheduled_at' => $scheduledItemPayload['scheduled_at'],
                    'status' => ScheduledPostStatus::Planned,
                ];

                if ($scheduledItemId !== null) {
                    $scheduledPost = $campaign->scheduledPosts()
                        ->where('user_id', $user->id)
                        ->whereKey((int) $scheduledItemId)
                        ->first();

                    if ($scheduledPost === null) {
                        throw ValidationException::withMessages([
                            'campaigns' => 'One or more scheduled items are invalid.',
                        ]);
                    }

                    $scheduledPost->update($attributes);
                    $scheduledItemIds[] = $scheduledPost->id;

                    continue;
                }

                $scheduledPost = $campaign->scheduledPosts()->create($attributes);
                $scheduledItemIds[] = $scheduledPost->id;
            }

            if ($pruneMissingItems) {
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

    private function resolveOrCreateCampaign(Proposal $proposal, User $user, array $campaignPayload): Campaign
    {
        $campaignId = $campaignPayload['id'] ?? null;

        if ($campaignId === null) {
            return Campaign::query()->create([
                'client_id' => $proposal->client_id,
                'proposal_id' => $proposal->id,
                'name' => $campaignPayload['name'],
                'description' => $campaignPayload['description'] ?? null,
            ]);
        }

        $campaign = Campaign::query()
            ->whereKey((int) $campaignId)
            ->where('client_id', $proposal->client_id)
            ->whereHas('client', fn ($query) => $query->where('user_id', $user->id))
            ->first();

        if ($campaign === null) {
            throw ValidationException::withMessages([
                'campaigns' => 'One or more campaigns are invalid for the selected client.',
            ]);
        }

        if ($campaign->proposal_id !== null && $campaign->proposal_id !== $proposal->id) {
            throw ValidationException::withMessages([
                'campaigns' => 'Selected campaigns must not already be linked to another proposal.',
            ]);
        }

        $campaign->update([
            'proposal_id' => $proposal->id,
            'name' => $campaignPayload['name'] ?? $campaign->name,
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

        $proposal->campaigns()
            ->when(
                $campaignIds !== [],
                fn ($query) => $query->whereNotIn('id', $campaignIds),
                fn ($query) => $query,
            )
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
}
