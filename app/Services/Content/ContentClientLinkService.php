<?php

namespace App\Services\Content;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\SocialAccountMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Validation\ValidationException;

class ContentClientLinkService
{
    public function link(User $user, SocialAccountMedia $media, Campaign $campaign, ?string $notes): void
    {
        $this->ensureCampaignOwnership($user, $campaign);
        $this->ensureMediaOwnership($user, $media);
        $this->ensureNotAlreadyLinked($campaign, $media);

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

    public function unlink(User $user, SocialAccountMedia $media, Client $client): void
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

    private function ensureMediaOwnership(User $user, SocialAccountMedia $media): void
    {
        $media->loadMissing('socialAccount');

        if ($media->socialAccount?->user_id !== $user->id) {
            throw new AuthorizationException('You are not allowed to modify this media-client link.');
        }
    }

    private function ensureNotAlreadyLinked(Campaign $campaign, SocialAccountMedia $media): void
    {
        $alreadyLinked = $campaign->instagramMedia()->whereKey($media->id)->exists();

        if ($alreadyLinked) {
            throw ValidationException::withMessages([
                'linkCampaignId' => 'This post is already linked to the selected campaign.',
            ]);
        }
    }
}
