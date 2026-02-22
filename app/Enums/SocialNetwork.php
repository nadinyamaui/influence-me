<?php

namespace App\Enums;

use App\Services\SocialMedia\Instagram\Client as InstagramClient;
use LogicException;

enum SocialNetwork: string
{
    case Tiktok = 'tiktok';
    case Instagram = 'instagram';
    case Youtube = 'youtube';
    case Twitch = 'twitch';

    public function oauthScopes(): array
    {
        return match ($this) {
            self::Instagram => [
                'instagram_basic',
                'instagram_manage_insights',
                'pages_show_list',
                'pages_read_engagement',
            ],
            default => [],
        };
    }

    public function socialiteDriver(): string
    {
        return match ($this) {
            self::Instagram => 'facebook',
            default => $this->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Tiktok => 'TikTok',
            self::Instagram => 'Instagram',
            self::Youtube => 'YouTube',
            self::Twitch => 'Twitch',
        };
    }

    public function socialiteClient(string $accessToken, ?string $userId = null): InstagramClient
    {
        return match ($this) {
            self::Instagram => app()->make(InstagramClient::class, [
                'access_token' => $accessToken,
                'user_id' => $userId,
            ]),
            default => throw new LogicException("{$this->label()} social login client is not configured."),
        };
    }
}
