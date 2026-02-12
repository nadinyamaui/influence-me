<?php

namespace App\Enums;

enum MediaType: string
{
    case Post = 'post';
    case Reel = 'reel';
    case Story = 'story';

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
}
