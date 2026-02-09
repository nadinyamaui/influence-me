<?php

namespace App\Policies;

use App\Models\InstagramAccount;
use App\Models\User;

class InstagramAccountPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InstagramAccount $instagramAccount): bool
    {
        return $user->id === $instagramAccount->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InstagramAccount $instagramAccount): bool
    {
        return $user->id === $instagramAccount->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InstagramAccount $instagramAccount): bool
    {
        if ($user->id !== $instagramAccount->user_id) {
            return false;
        }

        return $user->instagramAccounts()->count() > 1;
    }
}
