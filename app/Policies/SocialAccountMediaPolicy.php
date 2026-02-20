<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\SocialAccountMedia;
use App\Models\User;

class SocialAccountMediaPolicy
{
    public function view(User|ClientUser $user, SocialAccountMedia $instagramMedia): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->id === $instagramMedia->socialAccount->user_id;
    }

    public function linkToClient(User|ClientUser $user, SocialAccountMedia $instagramMedia): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->id === $instagramMedia->socialAccount->user_id;
    }
}
