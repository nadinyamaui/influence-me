<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\InstagramMedia;
use App\Models\User;

class InstagramMediaPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User|ClientUser $user, InstagramMedia $instagramMedia): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->id === $instagramMedia->instagramAccount->user_id;
    }

    /**
     * Determine whether the user can link media to a client campaign.
     */
    public function linkToClient(User|ClientUser $user, InstagramMedia $instagramMedia): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->id === $instagramMedia->instagramAccount->user_id;
    }
}
