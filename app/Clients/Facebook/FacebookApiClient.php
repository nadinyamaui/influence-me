<?php

namespace App\Clients\Facebook;

use App\Connectors\Facebook\FacebookGraphConnector;
use Illuminate\Support\Facades\Log;

class FacebookApiClient
{
    public function __construct(
        private readonly FacebookGraphConnector $connector,
    ) {}

    /**
     * @return object
     */
    public function exchangeForLongLivedToken(string $shortLivedToken): object
    {
        $response = $this->connector->get('/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $response->throw();

        return $response->object();
    }

    /**
     * @return object{id:string,username:string,name:?string,account_type:?string,profile_picture_url:?string}
     */
    public function resolveInstagramProfileFromAccessToken(string $accessToken): object
    {
        $pagesResponse = $this->connector->get('/me/accounts', [
            'access_token' => $accessToken,
            'fields' => 'id,name,instagram_business_account{id,username,name,profile_picture_url}',
            'limit' => 100,
        ]);

        $pagesResponse->throw();

        $pagesPayload = $pagesResponse->object();
        $pages = $pagesPayload?->data ?? [];

        if (! is_array($pages)) {
            $pages = [];
        }

        $instagramBusinessAccount = collect($pages)
            ->map(fn (mixed $page): ?object => $page instanceof \stdClass ? ($page?->instagram_business_account ?? null) : null)
            ->first(fn (mixed $account): bool => $account instanceof \stdClass && isset($account->id) && (string) $account->id !== '');

        if (! $instagramBusinessAccount instanceof \stdClass) {
            throw new \RuntimeException('No Instagram professional account is linked to this Meta/Facebook user.');
        }

        $instagramUserId = isset($instagramBusinessAccount->id) ? (string) $instagramBusinessAccount->id : '';

        $profileResponse = $this->connector->get('/'.$instagramUserId, [
            'access_token' => $accessToken,
            'fields' => 'id,username,name,account_type,profile_picture_url',
        ]);

        if ($profileResponse->failed()) {
            Log::warning('Failed to fetch Instagram profile detail after OAuth callback.', [
                'instagram_user_id' => $instagramUserId,
                'status' => $profileResponse->status(),
            ]);

            return (object) [
                'id' => $instagramUserId,
                'username' => isset($instagramBusinessAccount->username) ? (string) $instagramBusinessAccount->username : 'instagram_user',
                'name' => isset($instagramBusinessAccount->name) ? $instagramBusinessAccount->name : null,
                'account_type' => null,
                'profile_picture_url' => isset($instagramBusinessAccount->profile_picture_url) ? $instagramBusinessAccount->profile_picture_url : null,
            ];
        }

        $profile = $profileResponse->object();
        $instagramBusinessUsername = isset($instagramBusinessAccount->username) ? (string) $instagramBusinessAccount->username : 'instagram_user';
        $instagramBusinessName = isset($instagramBusinessAccount->name) ? $instagramBusinessAccount->name : null;
        $instagramBusinessProfilePicture = isset($instagramBusinessAccount->profile_picture_url) ? $instagramBusinessAccount->profile_picture_url : null;

        return (object) [
            'id' => isset($profile?->id) ? (string) $profile->id : $instagramUserId,
            'username' => isset($profile?->username) ? (string) $profile->username : $instagramBusinessUsername,
            'name' => isset($profile?->name) ? $profile->name : $instagramBusinessName,
            'account_type' => isset($profile?->account_type) ? $profile->account_type : null,
            'profile_picture_url' => isset($profile?->profile_picture_url) ? $profile->profile_picture_url : $instagramBusinessProfilePicture,
        ];
    }
}
