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
     * @return array<string, mixed>
     */
    public function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = $this->connector->get('/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array{id:string,username:string,name:?string,account_type:?string,profile_picture_url:?string}
     */
    public function resolveInstagramProfileFromAccessToken(string $accessToken): array
    {
        $pagesResponse = $this->connector->get('/me/accounts', [
            'access_token' => $accessToken,
            'fields' => 'id,name,instagram_business_account{id,username,name,profile_picture_url}',
            'limit' => 100,
        ]);

        $pagesResponse->throw();

        $pages = data_get($pagesResponse->json(), 'data', []);

        if (! is_array($pages)) {
            $pages = [];
        }

        $instagramBusinessAccount = collect($pages)
            ->map(fn (mixed $page): mixed => is_array($page) ? data_get($page, 'instagram_business_account') : null)
            ->first(fn (mixed $account): bool => is_array($account) && (string) data_get($account, 'id', '') !== '');

        if (! is_array($instagramBusinessAccount)) {
            throw new \RuntimeException('No Instagram professional account is linked to this Meta/Facebook user.');
        }

        $instagramUserId = (string) data_get($instagramBusinessAccount, 'id');

        $profileResponse = $this->connector->get('/'.$instagramUserId, [
            'access_token' => $accessToken,
            'fields' => 'id,username,name,account_type,profile_picture_url',
        ]);

        if ($profileResponse->failed()) {
            Log::warning('Failed to fetch Instagram profile detail after OAuth callback.', [
                'instagram_user_id' => $instagramUserId,
                'status' => $profileResponse->status(),
            ]);

            return [
                'id' => $instagramUserId,
                'username' => (string) data_get($instagramBusinessAccount, 'username', 'instagram_user'),
                'name' => data_get($instagramBusinessAccount, 'name'),
                'account_type' => null,
                'profile_picture_url' => data_get($instagramBusinessAccount, 'profile_picture_url'),
            ];
        }

        $profile = $profileResponse->json();

        return [
            'id' => (string) data_get($profile, 'id', $instagramUserId),
            'username' => (string) data_get($profile, 'username', data_get($instagramBusinessAccount, 'username', 'instagram_user')),
            'name' => data_get($profile, 'name', data_get($instagramBusinessAccount, 'name')),
            'account_type' => data_get($profile, 'account_type'),
            'profile_picture_url' => data_get($profile, 'profile_picture_url', data_get($instagramBusinessAccount, 'profile_picture_url')),
        ];
    }
}

