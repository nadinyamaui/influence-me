<?php

namespace App\Services\Proposals;

use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProposalWorkflowService
{
    public static function createDraftWithCampaignSchedule(User $user, array $validated): Proposal
    {
        return DB::transaction(function () use ($user, $validated): Proposal {
            $proposal = $user->proposals()->create([
                'client_id' => $validated['client_id'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'status' => ProposalStatus::Draft,
            ]);

            self::syncCampaignsAndSchedule($proposal, $user, $validated['campaigns']);

            return $proposal;
        });
    }

    public static function updateDraftWithCampaignSchedule(Proposal $proposal, array $validated): Proposal
    {
        return DB::transaction(function () use ($proposal, $validated): Proposal {
            $proposal->update([
                'client_id' => $validated['client_id'],
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);

            $user = $proposal->user;

            // Remove old campaigns and their scheduled posts for this proposal
            $existingCampaignIds = $proposal->campaigns()->pluck('id');

            $user->scheduledPosts()
                ->whereIn('campaign_id', $existingCampaignIds)
                ->delete();

            $proposal->campaigns()->delete();

            self::syncCampaignsAndSchedule($proposal, $user, $validated['campaigns']);

            return $proposal->fresh();
        });
    }

    private static function syncCampaignsAndSchedule(Proposal $proposal, User $user, array $campaignsData): void
    {
        foreach ($campaignsData as $campaignData) {
            $campaign = $proposal->campaigns()->create([
                'client_id' => $proposal->client_id,
                'name' => $campaignData['name'],
                'description' => $campaignData['description'] !== '' ? $campaignData['description'] : null,
            ]);

            foreach ($campaignData['scheduled_items'] as $itemData) {
                $user->scheduledPosts()->create([
                    'client_id' => $proposal->client_id,
                    'campaign_id' => $campaign->id,
                    'instagram_account_id' => $itemData['instagram_account_id'],
                    'title' => $itemData['title'],
                    'description' => $itemData['description'] !== '' ? $itemData['description'] : null,
                    'media_type' => $itemData['media_type'],
                    'scheduled_at' => $itemData['scheduled_at'],
                    'status' => ScheduledPostStatus::Planned,
                ]);
            }
        }
    }
}
