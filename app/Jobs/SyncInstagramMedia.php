<?php

namespace App\Jobs;

use App\Enums\MediaType;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Services\InstagramGraphService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncInstagramMedia implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public InstagramAccount $account
    ) {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $graphService = app(InstagramGraphService::class)->forAccount($this->account);
        $after = null;

        do {
            $payload = $graphService->getMedia($after);

            foreach ($payload['data'] ?? [] as $media) {
                InstagramMedia::updateOrCreate(
                    ['instagram_media_id' => $media['id']],
                    [
                        'instagram_account_id' => $this->account->id,
                        'media_type' => $this->mapMediaType($media),
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
        } while ($after !== null);
    }

    protected function mapMediaType(array $media): MediaType
    {
        $mediaType = $media['media_type'] ?? null;

        if ($mediaType === 'IMAGE' || $mediaType === 'CAROUSEL_ALBUM') {
            return MediaType::Post;
        }

        if ($mediaType === 'VIDEO') {
            if (($media['media_product_type'] ?? null) === 'REELS') {
                return MediaType::Reel;
            }

            if (str_contains((string) ($media['permalink'] ?? ''), '/reel/')) {
                return MediaType::Reel;
            }
        }

        return MediaType::Post;
    }
}
