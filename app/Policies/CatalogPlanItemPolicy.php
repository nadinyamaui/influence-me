<?php

namespace App\Policies;

use App\Models\CatalogPlanItem;
use App\Models\ClientUser;
use App\Models\User;

class CatalogPlanItemPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, CatalogPlanItem $catalogPlanItem): bool
    {
        return $user instanceof User
            && $user->id === $catalogPlanItem->catalogPlan?->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, CatalogPlanItem $catalogPlanItem): bool
    {
        return $user instanceof User
            && $user->id === $catalogPlanItem->catalogPlan?->user_id;
    }

    public function delete(User|ClientUser $user, CatalogPlanItem $catalogPlanItem): bool
    {
        return $user instanceof User
            && $user->id === $catalogPlanItem->catalogPlan?->user_id;
    }
}
