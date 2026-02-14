<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\ClientUser;
use App\Models\Proposal;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, Campaign $campaign): bool
    {
        return $user instanceof User && $user->id === $campaign->client->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, Campaign $campaign): bool
    {
        return $user instanceof User && $user->id === $campaign->client->user_id;
    }

    public function delete(User|ClientUser $user, Campaign $campaign): bool
    {
        return $user instanceof User && $user->id === $campaign->client->user_id;
    }

    public function linkProposal(User|ClientUser $user, Campaign $campaign, Proposal $proposal): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->id === $campaign->client->user_id
            && $proposal->user_id === $user->id
            && $proposal->client_id === $campaign->client_id;
    }
}
