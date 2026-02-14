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

        $name = trim((string) ($campaignName ?? ''));
        $resolvedCampaignName = $name !== '' ? $name : 'Uncategorized';

        $campaign = $client->campaigns()->firstOrCreate(
            ['name' => $resolvedCampaignName],
            ['proposal_id' => null, 'description' => null],
        );

        $campaign->instagramMedia()->syncWithoutDetaching([
            $media->id => ['notes' => $notes],
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

        $client->campaigns()
            ->with('instagramMedia')
            ->get()
            ->each(fn ($campaign): int => $campaign->instagramMedia()->detach($media->id));
    }

    private function ensureOwnership(User $user, InstagramMedia $media, Client $client): void
    {
        $media->loadMissing('instagramAccount');

        $ownsClient = $client->user_id === $user->id;
        $ownsMedia = $media->instagramAccount?->user_id === $user->id;

        if (! $ownsClient || ! $ownsMedia) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }
}
