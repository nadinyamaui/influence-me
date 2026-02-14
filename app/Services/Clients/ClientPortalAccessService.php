<?php

namespace App\Services\Clients;

use App\Mail\ClientPortalInvitation;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientPortalAccessService
{
    public function invite(Client $client, string $influencerName): ClientUser
    {
        if (blank($client->email)) {
            throw ValidationException::withMessages([
                'invite' => 'Add an email to enable portal access.',
            ]);
        }

        if ($client->clientUser()->exists()) {
            throw ValidationException::withMessages([
                'invite' => 'Portal access is already active for this client.',
            ]);
        }

        $existingPortalUser = ClientUser::query()
            ->where('email', $client->email)
            ->exists();

        if ($existingPortalUser) {
            throw ValidationException::withMessages([
                'invite' => 'A portal user with this email already exists.',
            ]);
        }

        $temporaryPassword = Str::random(16);

        $clientUser = $client->clientUser()->create([
            'name' => $client->name,
            'email' => $client->email,
            'password' => $temporaryPassword,
        ]);

        Mail::to($clientUser->email)->send(new ClientPortalInvitation(
            influencerName: $influencerName,
            temporaryPassword: $temporaryPassword,
            loginUrl: route('portal.login'),
        ));

        return $clientUser;
    }

    public function revoke(Client $client): void
    {
        $clientUser = $client->clientUser;

        if ($clientUser === null) {
            throw ValidationException::withMessages([
                'revoke' => 'Portal access is not active for this client.',
            ]);
        }

        $clientUser->delete();
    }
}
