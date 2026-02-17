<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\ProposalLineItem;
use App\Models\User;

class ProposalLineItemPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, ProposalLineItem $proposalLineItem): bool
    {
        return $user instanceof User
            && $user->id === $proposalLineItem->proposal?->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, ProposalLineItem $proposalLineItem): bool
    {
        return $user instanceof User
            && $user->id === $proposalLineItem->proposal?->user_id;
    }

    public function delete(User|ClientUser $user, ProposalLineItem $proposalLineItem): bool
    {
        return $user instanceof User
            && $user->id === $proposalLineItem->proposal?->user_id;
    }
}
