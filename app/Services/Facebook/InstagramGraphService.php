<?php

namespace App\Services\Facebook;

use App\Enums\AccountType;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Enums\MediaType;
use App\Models\InstagramAccount;
use Carbon\Carbon;
use FacebookAds\Http\Exception\AuthorizationException;
use FacebookAds\Http\Exception\RequestException;

class InstagramGraphService
{
    protected Client $client;

    public function __construct(protected InstagramAccount $account)
    {
        $this->client = app(Client::class, [
            'user_id' => $this->account->instagram_user_id,
            'access_token' => $this->account->access_token,
        ]);
    }

    public function retrieveMedia(): void
    {
        $mediaPosts = $this->client->getAllMedia();
        foreach ($mediaPosts as $media) {
            $this->account->instagramMedia()->updateOrCreate([
                'instagram_media_id' => $media['id'],
            ], [
                'instagram_account_id' => $this->account->id,
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
            'username' => $profile['username'] ?? null,
            'name' => $profile['name'] ?? null,
            'biography' => $profile['biography'] ?? null,
            'profile_picture_url' => $profile['profile_picture_url'] ?? null,
            'followers_count' => $profile['followers_count'] ?? 0,
            'following_count' => $profile['following_count'] ?? 0,
            'media_count' => $profile['media_count'] ?? 0,
            'account_type' => $this->mapAccountType($profile['account_type'] ?? null),
        ];
    }

    protected function mapAccountType(?string $accountType): AccountType
    {
        $normalizedType = strtolower((string) $accountType);

        return match ($normalizedType) {
            'media_creator', 'creator' => AccountType::Creator,
            default => AccountType::Business,
        };
    }
}
