<?php

namespace App\Services\SocialMedia;

use App\Enums\SocialNetwork;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\InstagramGraphService;
use InvalidArgumentException;

class SocialMediaManager
{
    public function forAccount(SocialAccount $account): SocialMediaInterface
    {
        $network = $account->social_network;
        if (! $network instanceof SocialNetwork) {
            throw new InvalidArgumentException('No social media service is configured because the account network is invalid.');
        }

        return match ($network) {
            SocialNetwork::Instagram => app(InstagramGraphService::class, ['account' => $account]),
            SocialNetwork::Tiktok,
            SocialNetwork::Youtube,
            SocialNetwork::Twitch => throw new InvalidArgumentException(
                "No social media service is configured for network [{$network->value}]."
            ),
        };
    }
}
