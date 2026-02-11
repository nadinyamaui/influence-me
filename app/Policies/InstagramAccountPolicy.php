<?php

namespace App\Policies;

use App\Models\InstagramAccount;
use App\Models\User;

class InstagramAccountPolicy
{
    public function view(User $user, InstagramAccount $instagramAccount): bool
    {
        return $user->id === $instagramAccount->user_id;
    }

    public function update(User $user, InstagramAccount $instagramAccount): bool
    {
        return $user->id === $instagramAccount->user_id;
    }

    public function delete(User $user, InstagramAccount $instagramAccount): bool
    {
        if ($user->id !== $instagramAccount->user_id) {
            return false;
        }

        return $user->instagramAccounts()->count() > 1;
    }
}
