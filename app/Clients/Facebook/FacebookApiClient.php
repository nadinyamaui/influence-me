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
            ->first(fn (mixed $account): bool => $account instanceof \stdClass && $this->stringProp($account, 'id') !== '');

        if (! $instagramBusinessAccount instanceof \stdClass) {
            throw new \RuntimeException('No Instagram professional account is linked to this Meta/Facebook user.');
        }

        $instagramUserId = $this->stringProp($instagramBusinessAccount, 'id');

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
                'username' => $this->stringProp($instagramBusinessAccount, 'username', 'instagram_user'),
                'name' => $this->mixedProp($instagramBusinessAccount, 'name'),
                'account_type' => null,
                'profile_picture_url' => $this->mixedProp($instagramBusinessAccount, 'profile_picture_url'),
            ];
        }

        $profile = $profileResponse->object();

        return (object) [
            'id' => $this->stringProp($profile, 'id', $instagramUserId),
            'username' => $this->stringProp($profile, 'username', $this->stringProp($instagramBusinessAccount, 'username', 'instagram_user')),
            'name' => $this->mixedProp($profile, 'name', $this->mixedProp($instagramBusinessAccount, 'name')),
            'account_type' => $this->mixedProp($profile, 'account_type'),
            'profile_picture_url' => $this->mixedProp($profile, 'profile_picture_url', $this->mixedProp($instagramBusinessAccount, 'profile_picture_url')),
        ];
    }

    private function stringProp(?object $object, string $property, string $default = ''): string
    {
        return isset($object?->{$property}) ? (string) $object?->{$property} : $default;
    }

    private function mixedProp(?object $object, string $property, mixed $default = null): mixed
    {
        return isset($object?->{$property}) ? $object?->{$property} : $default;
    }
}
