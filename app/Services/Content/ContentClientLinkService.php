<?php

namespace App\Services\Content;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ContentClientLinkService
{
    public function link(User $user, InstagramMedia $media, Campaign $campaign, ?string $notes): void
    {
        $this->ensureCampaignOwnership($user, $campaign);
        $this->ensureMediaOwnership($user, $media);

        $campaign->instagramMedia()->syncWithoutDetaching([
            $media->id => ['notes' => $notes],
        ]);
    }

    public function batchLink(User $user, EloquentCollection $mediaItems, Campaign $campaign, ?string $notes): void
    {
        foreach ($mediaItems as $media) {
            $this->link($user, $media, $campaign, $notes);
        }
    }

    public function unlink(User $user, InstagramMedia $media, Client $client): void
    {
        $this->ensureClientOwnership($user, $client);
        $this->ensureMediaOwnership($user, $media);

        $client->campaigns()
            ->with('instagramMedia')
            ->get()
            ->each(fn ($campaign): int => $campaign->instagramMedia()->detach($media->id));
    }

    private function ensureCampaignOwnership(User $user, Campaign $campaign): void
    {
        $campaign->loadMissing('client');

        if ($campaign->client?->user_id !== $user->id) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }

    private function ensureClientOwnership(User $user, Client $client): void
    {
        if ($client->user_id !== $user->id) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }

    private function ensureMediaOwnership(User $user, InstagramMedia $media): void
    {
        $media->loadMissing('instagramAccount');

        if ($media->instagramAccount?->user_id !== $user->id) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }
}
