<?php

namespace App\Services\SocialMedia\Tiktok;

use App\Enums\SocialNetwork;
use App\Services\Auth\SocialiteClient;
use Illuminate\Support\Collection;

class Client implements SocialiteClient
{
    public function __construct(
        protected string $access_token,
        protected ?string $user_id = null,
    ) {}

    public function getLongLivedToken(): array
    {
        return [
            'access_token' => $this->access_token,
        ];
    }

    public function accounts(): Collection
    {
        $payload = $this->connector()->get('/v2/user/info/', [
            'fields' => implode(',', [
                'open_id',
                'display_name',
                'avatar_url',
                'bio_description',
                'follower_count',
                'following_count',
                'video_count',
            ]),
        ]);

        $user = $payload['user'] ?? [];
        $socialNetworkUserId = $user['open_id'] ?? $this->user_id;
        if (! is_string($socialNetworkUserId) || $socialNetworkUserId === '') {
            return collect();
        }

        $name = (string) ($user['display_name'] ?? '');
        $username = $name !== '' ? $name : $socialNetworkUserId;
        $biography = (string) ($user['bio_description'] ?? '');

        return collect([
            [
                'social_network' => SocialNetwork::Tiktok->value,
                'social_network_user_id' => $socialNetworkUserId,
                'name' => $name !== '' ? $name : null,
                'username' => $username,
                'biography' => $biography !== '' ? $biography : null,
                'profile_picture_url' => $user['avatar_url'] ?? null,
                'followers_count' => (int) ($user['follower_count'] ?? 0),
                'following_count' => (int) ($user['following_count'] ?? 0),
                'media_count' => (int) ($user['video_count'] ?? 0),
                'access_token' => $this->access_token,
            ],
        ]);
    }

    public function getAllMedia(): Collection
    {
        $videos = collect();
        $cursor = 0;

        do {
            $payload = $this->connector()->request('POST', '/v2/video/list/', [
                'query' => [
                    'fields' => implode(',', [
                        'id',
                        'title',
                        'video_description',
                        'duration',
                        'cover_image_url',
                        'embed_link',
                        'share_url',
                        'like_count',
                        'comment_count',
                        'share_count',
                        'view_count',
                        'create_time',
                    ]),
                ],
                'json' => [
                    'max_count' => 20,
                    'cursor' => $cursor,
                ],
            ]);

            $pageVideos = collect($payload['videos'] ?? [])
                ->filter(fn (mixed $video): bool => is_array($video) && ($video['id'] ?? null) !== null)
                ->values();

            if ($pageVideos->isEmpty()) {
                break;
            }

            $videos = $videos->concat($pageVideos);
            $nextCursor = (int) ($payload['cursor'] ?? $cursor);
            $hasMore = (bool) ($payload['has_more'] ?? false);

            if (! $hasMore || $nextCursor === $cursor) {
                break;
            }

            $cursor = $nextCursor;
        } while (true);

        return $videos->unique(fn (array $video): string => (string) $video['id'])->values();
    }

    public function getMediaStats(Collection $videoIds): Collection
    {
        return $videoIds
            ->filter(fn (mixed $id): bool => is_string($id) && $id !== '')
            ->values()
            ->chunk(20)
            ->flatMap(function (Collection $chunk): Collection {
                $payload = $this->connector()->request('POST', '/v2/video/query/', [
                    'query' => [
                        'fields' => implode(',', [
                            'id',
                            'like_count',
                            'comment_count',
                            'share_count',
                            'view_count',
                            'create_time',
                            'title',
                        ]),
                    ],
                    'json' => [
                        'filters' => [
                            'video_ids' => $chunk->all(),
                        ],
                    ],
                ]);

                return collect($payload['videos'] ?? [])
                    ->filter(fn (mixed $video): bool => is_array($video) && ($video['id'] ?? null) !== null)
                    ->mapWithKeys(fn (array $video): array => [
                        (string) $video['id'] => [
                            'id' => (string) $video['id'],
                            'like_count' => (int) ($video['like_count'] ?? 0),
                            'comment_count' => (int) ($video['comment_count'] ?? 0),
                            'share_count' => (int) ($video['share_count'] ?? 0),
                            'view_count' => (int) ($video['view_count'] ?? 0),
                            'create_time' => $video['create_time'] ?? null,
                            'title' => $video['title'] ?? null,
                        ],
                    ]);
            });
    }

    protected function connector(): TikTokApiConnector
    {
        return app()->make(TikTokApiConnector::class, [
            'accessToken' => $this->access_token,
        ]);
    }
}
