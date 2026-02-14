<?php

namespace App\Services\Content;

use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ContentClientLinkService
{
    public function link(User $user, InstagramMedia $media, Client $client, ?string $campaignName, ?string $notes): void
    {
        $this->ensureOwnership($user, $media, $client);

        $media->clients()->syncWithoutDetaching([
            $client->id => [
                'campaign_name' => $campaignName,
                'notes' => $notes,
            ],
        ]);
    }

    public function batchLink(User $user, EloquentCollection $mediaItems, Client $client, ?string $campaignName, ?string $notes): void
    {
        foreach ($mediaItems as $media) {
            $this->link($user, $media, $client, $campaignName, $notes);
        }
    }

    public function unlink(User $user, InstagramMedia $media, Client $client): void
    {
        $this->ensureOwnership($user, $media, $client);

        $media->clients()->detach($client->id);
    }

    private function ensureOwnership(User $user, InstagramMedia $media, Client $client): void
    {
        $media->loadMissing('instagramAccount');

        $ownsClient = $client->user_id === $user->id;
        $ownsMedia = $media->instagramAccount->user_id === $user->id;

        if (! $ownsClient || ! $ownsMedia) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }
}
