<?php

namespace App\Enums;

enum PlatformType: string
{
    case Instagram = 'instagram';
    case TikTok = 'tiktok';

    public static function values(): array
    {
        return array_map(
            static fn (PlatformType $platform): string => $platform->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::TikTok => 'TikTok',
        };
    }
}
