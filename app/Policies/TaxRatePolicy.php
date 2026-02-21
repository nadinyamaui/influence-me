<?php

namespace App\Policies;

use App\Models\ClientUser;
use App\Models\TaxRate;
use App\Models\User;

class TaxRatePolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, TaxRate $taxRate): bool
    {
        return $user instanceof User && $user->id === $taxRate->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, TaxRate $taxRate): bool
    {
        return $user instanceof User && $user->id === $taxRate->user_id;
    }

    public function delete(User|ClientUser $user, TaxRate $taxRate): bool
    {
        return $user instanceof User && $user->id === $taxRate->user_id;
    }
}
