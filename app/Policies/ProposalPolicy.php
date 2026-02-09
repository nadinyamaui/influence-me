<?php

namespace App\Policies;

use App\Enums\ProposalStatus;
use App\Models\ClientUser;
use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|ClientUser $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|ClientUser $user, Proposal $proposal): bool
    {
        if ($user instanceof ClientUser) {
            return $user->client_id === $proposal->client_id;
        }

        return $user->id === $proposal->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User && $user->id === $proposal->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User && $user->id === $proposal->user_id;
    }

    /**
     * Determine whether the user can send the proposal.
     */
    public function send(User|ClientUser $user, Proposal $proposal): bool
    {
        return $user instanceof User
            && $user->id === $proposal->user_id
            && $proposal->status === ProposalStatus::Draft;
    }
}
