<?php

namespace App\Policies;

use App\Models\CatalogProduct;
use App\Models\ClientUser;
use App\Models\User;

class CatalogProductPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, CatalogProduct $catalogProduct): bool
    {
        return $user instanceof User && $user->id === $catalogProduct->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, CatalogProduct $catalogProduct): bool
    {
        return $user instanceof User && $user->id === $catalogProduct->user_id;
    }

    public function delete(User|ClientUser $user, CatalogProduct $catalogProduct): bool
    {
        return $user instanceof User && $user->id === $catalogProduct->user_id;
    }
}
