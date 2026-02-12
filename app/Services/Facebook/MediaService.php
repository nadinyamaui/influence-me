<?php

namespace App\Services\Facebook;

use App\Enums\MediaType;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Carbon\Carbon;

class MediaService
{
    protected Client $client;

    public function __construct(protected InstagramAccount $account)
    {
        $this->client = app(Client::class, ['access_token' => $this->account->access_token]);
    }

    public function retrieveMedia(): void
    {
        $after = null;
        do {
            $payload = $this->client->getMedia($after);

            foreach ($payload['data'] ?? [] as $media) {
                InstagramMedia::updateOrCreate(
                    ['instagram_media_id' => $media['id']],
                    [
                        'instagram_account_id' => $this->account->id,
                        'media_type' => MediaType::parse($media),
                        'caption' => $media['caption'] ?? null,
                        'permalink' => $media['permalink'] ?? null,
                        'media_url' => $media['media_url'] ?? null,
                        'thumbnail_url' => $media['thumbnail_url'] ?? null,
                        'published_at' => isset($media['timestamp']) ? Carbon::parse($media['timestamp']) : null,
                        'like_count' => $media['like_count'] ?? 0,
                        'comments_count' => $media['comments_count'] ?? 0,
                    ]
                );
            }

            $after = $payload['paging']['cursors']['after'] ?? null;
        } while ($after);
    }
}
