<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\ScheduledPost;
use App\Models\User;

class ScheduledPostPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
    }

    public function delete(User|ClientUser $user, ScheduledPost $scheduledPost): bool
    {
        return $user instanceof User && $user->id === $scheduledPost->user_id;
    }
}
