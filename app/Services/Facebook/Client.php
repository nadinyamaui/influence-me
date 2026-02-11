<?php

namespace App\Services\Facebook;

use FacebookAds\Api;
use Illuminate\Support\Collection;

class Client
{
    protected Api $api;

    public function __construct(protected string $access_token)
    {
        $this->api = Api::init(config('services.facebook.client_id'), config('services.facebook.client_secret'), $access_token);
        $this->api->setDefaultGraphVersion('24.0');
    }

    public function getLongLivedToken(): array
    {
        return $this->api->call('/oauth/access_token', params: [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $this->access_token,
        ])->getContent();
    }

    public function accounts(): Collection
    {
        $accounts = $this->api->call('/me/accounts', params: [
            'fields' => '
                id,
                name,
                access_token,
                category,
                followers_count,
                verification_status,
                instagram_business_account{
                    id,
                    username,
                    name,
                    biography,
                    profile_picture_url,
                    followers_count,
                    follows_count,
                    media_count
                }',
        ])->getContent();

        return collect($accounts['data'])
            ->filter(fn ($account) => isset($account['instagram_business_account']['id']))
            ->map(function ($account) {
                $ig = $account['instagram_business_account'];

                return [
                    'instagram_user_id' => $ig['id'],
                    'name' => $ig['name'],
                    'username' => $ig['username'],
                    'biography' => trim($ig['biography'] ?? ''),
                    'profile_picture_url' => $ig['profile_picture_url'],
                    'followers_count' => $ig['followers_count'],
                    'following_count' => $ig['follows_count'],
                    'media_count' => $ig['media_count'],
                    'access_token' => $account['access_token'],
                ];
            });
    }
}
