<?php

namespace App\Policies;

use App\Models\SocialAccount;
use App\Models\User;

class SocialAccountPolicy
{
    public function view(User $user, SocialAccount $socialAccount): bool
    {
        return $user->id === $socialAccount->user_id;
    }

    public function update(User $user, SocialAccount $socialAccount): bool
    {
        return $user->id === $socialAccount->user_id;
    }

    public function delete(User $user, SocialAccount $socialAccount): bool
    {
        if ($user->id !== $socialAccount->user_id) {
            return false;
        }

        return $user->socialAccounts()->count() > 1;
    }
}
