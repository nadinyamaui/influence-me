<?php

namespace App\Enums;

enum MediaType: string
{
    case Post = 'post';
    case Reel = 'reel';
    case Story = 'story';

    public static function filters(): array
    {
        return array_merge(
            ['all'],
            array_map(
                static fn (MediaType $mediaType): string => $mediaType->value,
                MediaType::cases(),
            ),
        );
    }

    public static function parse(array $media): MediaType
    {
        $mediaType = $media['media_type'] ?? null;
        if ($mediaType === 'IMAGE' || $mediaType === 'CAROUSEL_ALBUM') {
            return MediaType::Post;
        }
        if ($mediaType !== 'VIDEO') {
            return MediaType::Post;
        }
        if (($media['media_product_type'] ?? null) === 'REELS') {
            return MediaType::Reel;
        }
        if (str_contains($media['permalink'] ?? '', '/reel/')) {
            return MediaType::Reel;
        }

        return MediaType::Post;
    }

    public function metrics()
    {
        return match ($this) {
            self::Post => [
                'reach',
                'likes',
                'comments',
                'shares',
                'saved',
                'views',
                'total_interactions',
            ],

            self::Reel => [
                'views',
                'reach',
                'total_interactions',
                'ig_reels_avg_watch_time',
                'ig_reels_video_view_total_time',
                'reels_skip_rate',
            ],

            self::Story => [
                'reach',
                'replies',
                'navigation',
            ],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Reel => 'Reel',
            self::Story => 'Story',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Post => 'Posts',
            self::Reel => 'Reels',
            self::Story => 'Stories',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Post => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
            self::Reel => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-200',
            self::Story => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        };
    }

    public function chartColor(): string
    {
        return match ($this) {
            self::Post => '#3b82f6',
            self::Reel => '#8b5cf6',
            self::Story => '#f59e0b',
        };
    }
}
