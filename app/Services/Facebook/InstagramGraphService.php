<?php

namespace App\Services\Facebook;

use App\Enums\MediaType;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use App\Services\SocialMedia\SocialMediaInterface;
use Carbon\Carbon;
use FacebookAds\Http\Exception\AuthorizationException;
use FacebookAds\Http\Exception\RequestException;

class InstagramGraphService implements SocialMediaInterface
{
    protected Client $client;

    public function __construct(protected SocialAccount $account)
    {
        $this->client = app(Client::class, [
            'user_id' => $this->account->social_network_user_id,
            'access_token' => $this->account->access_token,
        ]);
    }

    public function retrieveMedia(): void
    {
        $mediaPosts = $this->client->getAllMedia();
        foreach ($mediaPosts as $media) {
            $this->account->instagramMedia()->updateOrCreate([
                'social_account_media_id' => $media['id'],
            ], [
                'social_account_id' => $this->account->id,
                'media_type' => MediaType::parse($media),
                'caption' => $media['caption'] ?? null,
                'permalink' => $media['permalink'] ?? null,
                'media_url' => $media['media_url'] ?? null,
                'thumbnail_url' => $media['thumbnail_url'] ?? null,
                'published_at' => Carbon::parse($media['timestamp']),
                'like_count' => $media['like_count'] ?? 0,
                'comments_count' => $media['comments_count'] ?? 0,
            ]);
        }
    }

    public function syncMediaInsights(): void
    {
        $this->account->instagramMedia()
            ->where('published_at', '>=', now()->subDays(90))
            ->where('media_type', '!=', MediaType::Story->value)
            ->chunkById(50, function ($mediaItems): void {
                $mediaItems->each(function (SocialAccountMedia $media): void {
                    $insights = $this->client->getMediaInsights($media->social_account_media_id, $media->media_type);
                    $reach = (int) ($insights->get('reach') ?? 0);
                    $saved = (int) ($insights->get('saved') ?? 0);
                    $shares = (int) ($insights->get('shares') ?? 0);
                    $engagementRate = 0;
                    if ($reach > 0) {
                        $engagementRate = (($media->like_count + $media->comments_count + $saved + $shares) / $reach) * 100;
                    }

                    $media->update([
                        'reach' => $reach,
                        'impressions' => $insights->get('views'),
                        'saved_count' => $saved,
                        'shares_count' => $shares,
                        'engagement_rate' => $engagementRate,
                    ]);
                    usleep(100000);
                });
            });
    }

    public function syncStories(): void
    {
        $stories = $this->client->getStories();
        foreach ($stories as $story) {
            $this->account->instagramMedia()->updateOrCreate([
                'social_account_media_id' => $story['id'],
            ], [
                'social_account_id' => $this->account->id,
                'media_type' => MediaType::Story,
                'caption' => $story['caption'] ?? null,
                'permalink' => $story['permalink'],
                'media_url' => $story['media_url'],
                'published_at' => Carbon::parse($story['timestamp']),
            ]);
        }
    }

    public function getProfile(): array
    {
        try {
            $profile = $this->client->getProfile();
        } catch (AuthorizationException $exception) {
            throw new InstagramTokenExpiredException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (RequestException $exception) {
            throw new InstagramApiException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return [
            'username' => $profile['username'] ?? $this->account->username,
            'name' => $profile['name'] ?? $this->account->name,
            'biography' => $profile['biography'] ?? $this->account->biography,
            'profile_picture_url' => $profile['profile_picture_url'] ?? $this->account->profile_picture_url,
            'followers_count' => $profile['followers_count'] ?? $this->account->followers_count,
            'following_count' => $profile['following_count'] ?? $this->account->following_count,
            'media_count' => $profile['media_count'] ?? $this->account->media_count,
        ];
    }

    public function refreshLongLivedToken(): string
    {
        try {
            $response = $this->client->refreshLongLivedToken();
        } catch (AuthorizationException $exception) {
            throw new InstagramTokenExpiredException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (RequestException $exception) {
            throw new InstagramApiException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $accessToken = $response['access_token'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            throw new InstagramApiException('Instagram token refresh response was missing access_token.');
        }

        return $accessToken;
    }

    public function syncAudienceDemographics(): void
    {
        if (($this->account->followers_count ?? 0) < 100) {
            return;
        }

        $audiences = collect($this->client->getAudienceDemographics());
        $recordedAt = now();

        $this->account->audienceDemographics()->delete();

        $audiences
            ->each(function ($audience, $key) use ($recordedAt) {
                $totalForType = collect($audience)->sum();
                foreach ($audience as $dimension => $value) {
                    $this->account->audienceDemographics()->create([
                        'type' => $key,
                        'dimension' => $dimension,
                        'value' => $totalForType > 0 ? ($value * 100 / $totalForType) : 0,
                        'recorded_at' => $recordedAt,
                    ]);
                }
            });
    }
}
