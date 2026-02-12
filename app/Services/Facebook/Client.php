<?php

namespace App\Services\Facebook;

use FacebookAds\Api;
use FacebookAds\Object\IGUser;
use FacebookAds\Object\Page;
use FacebookAds\Object\User;
use Illuminate\Support\Collection;

class Client
{
    protected Api $api;

    public function __construct(protected string $access_token, protected ?string $user_id = null)
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
        $user = new User($this->user_id);
        $accounts = $user->getAccounts([
            'id',
            'name',
            'access_token',
            'category',
            'followers_count',
            'verification_status',
            'instagram_business_account{id,username,name,biography,profile_picture_url,followers_count,follows_count,media_count}',

        ]);

        return collect($accounts->getArrayCopy())
            ->filter(fn(Page $page) => isset($page->getData()['instagram_business_account']['id']))
            ->map(function (Page $page) {
                $account = $page->getData();
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

    public function getAllMedia(): array
    {
        $igUser = new IGUser($this->user_id);
        $cursor = $igUser->getMedia([
            'id',
            'caption',
            'media_type',
            'media_product_type',
            'media_url',
            'thumbnail_url',
            'permalink',
            'timestamp',
            'like_count',
            'comments_count',
        ]);
        $cursor->setUseImplicitFetch(true);
        $allMedia = [];
        foreach ($cursor as $media) {
            $allMedia[] = $media->exportAllData();
        }

        return $allMedia;
    }

    public function getProfile(): array
    {
        $igUser = new IGUser($this->user_id);
        $profile = $igUser->getSelf([
            'id',
            'username',
            'name',
            'biography',
            'profile_picture_url',
            'followers_count',
            'follows_count',
            'media_count',
            'account_type',
        ])->exportAllData();

        return [
            'id' => $profile['id'] ?? null,
            'username' => $profile['username'] ?? null,
            'name' => $profile['name'] ?? null,
            'biography' => $profile['biography'] ?? null,
            'profile_picture_url' => $profile['profile_picture_url'] ?? null,
            'followers_count' => $profile['followers_count'] ?? null,
            'following_count' => $profile['follows_count'] ?? null,
            'media_count' => $profile['media_count'] ?? null,
            'account_type' => $profile['account_type'] ?? null,
        ];
    }
}
