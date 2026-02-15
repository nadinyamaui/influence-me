<?php

namespace App\Policies;

use App\Enums\ProposalStatus;
use App\Models\ClientUser;
use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return true;
    }

    public function view(User|ClientUser $user, Proposal $proposal): bool
    {
        if ($user instanceof ClientUser) {
            return $user->client_id === $proposal->client_id;
        }

        return $user->id === $proposal->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User
            && $user->id === $proposal->user_id
            && in_array($proposal->status, [ProposalStatus::Draft, ProposalStatus::Revised], true);
    }

    public function delete(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User && $user->id === $proposal->user_id;
    }

    public function send(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User
            && $user->id === $proposal->user_id
            && $proposal->status === ProposalStatus::Draft;
    }
}
