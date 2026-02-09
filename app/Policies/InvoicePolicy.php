<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\ClientUser;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|ClientUser $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|ClientUser $user, Invoice $invoice): bool
    {
        if ($user instanceof ClientUser) {
            return $user->client_id === $invoice->client_id;
        }

        return $user->id === $invoice->user_id;
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
    public function update(User|ClientUser $user, Invoice $invoice): bool
    {
        return $user instanceof User
            && $user->id === $invoice->user_id
            && $invoice->status === InvoiceStatus::Draft;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|ClientUser $user, Invoice $invoice): bool
    {
        return $user instanceof User
            && $user->id === $invoice->user_id
            && $invoice->status === InvoiceStatus::Draft;
    }
}
