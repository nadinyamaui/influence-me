<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;

class ClientPolicy
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
    public function view(User|ClientUser $user, Client $client): bool
    {
        if ($user instanceof ClientUser) {
            return $user->client_id === $client->id;
        }

        return $user->id === $client->user_id;
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
    public function update(User|ClientUser $user, Client $client): bool
    {
        return $user instanceof User && $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|ClientUser $user, Client $client): bool
    {
        return $user instanceof User && $user->id === $client->user_id;
    }
}
