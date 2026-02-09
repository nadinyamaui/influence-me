<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\ScheduledPost;
use App\Models\User;

class ScheduledPostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
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
    public function update(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
    }
}
