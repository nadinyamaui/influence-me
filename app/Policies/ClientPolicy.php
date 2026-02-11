<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function view(User|ClientUser $user, Client $client): bool
    {
        if ($user instanceof ClientUser) {
            return $user->client_id === $client->id;
        }

        return $user->id === $client->user_id;
    }

    public function create(User|ClientUser $user): bool
    {
        return $user instanceof User;
    }

    public function update(User|ClientUser $user, Client $client): bool
    {
        return $user instanceof User && $user->id === $client->user_id;
    }

    public function delete(User|ClientUser $user, Client $client): bool
    {
        return $user instanceof User && $user->id === $client->user_id;
    }
}
