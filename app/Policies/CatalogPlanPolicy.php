<?php

namespace App\Policies;

use App\Models\CatalogPlan;
use App\Models\ClientUser;
use App\Models\User;

class CatalogPlanPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, CatalogPlan $catalogPlan): bool
    {
        return $user instanceof User && $user->id === $catalogPlan->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, CatalogPlan $catalogPlan): bool
    {
        return $user instanceof User && $user->id === $catalogPlan->user_id;
    }

    public function delete(User|ClientUser $user, CatalogPlan $catalogPlan): bool
    {
        return $user instanceof User && $user->id === $catalogPlan->user_id;
    }
}
