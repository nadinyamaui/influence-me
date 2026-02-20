<?php

namespace App\Builders;

use App\Enums\AnalyticsPeriod;
use App\Enums\AnalyticsTopContentSort;
use App\Enums\MediaType;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InstagramMediaBuilder extends Builder
{
    public function forClient(int $clientId): self
    {
        return $this->whereHas('campaigns', fn (Builder $builder): Builder => $builder->where('campaigns.client_id', $clientId));
    }

    public function latestPublished(): self
    {
        return $this->orderByDesc('published_at');
    }

    public function distinctMediaRows(): self
    {
        return $this->select('instagram_media.*')->distinct();
    }

    public function withSocialAccount(): self
    {
        return $this->with('socialAccount');
    }

    public function withCampaignsForClient(int $clientId): self
    {
        return $this->with([
            'campaigns' => fn ($builder) => $builder
                ->where('campaigns.client_id', $clientId)
                ->select('campaigns.id', 'campaigns.name'),
        ]);
    }

    public function forUser(int $userId): self
    {
        return $this->whereHas('socialAccount', fn (Builder $builder): Builder => $builder->where('user_id', $userId));
    }

    public function forAnalyticsSummary(): self
    {
        return $this->select([
            'instagram_media.id',
            'instagram_media.social_account_id',
            'instagram_media.published_at',
            'instagram_media.reach',
            'instagram_media.impressions',
            'instagram_media.engagement_rate',
        ]);
    }

    public function publishedChronologically(): self
    {
        return $this->orderBy('published_at')
            ->orderBy('id');
    }

    public function filterByMediaType(string $mediaType): self
    {
        if ($mediaType === 'all') {
            return $this;
        }

        if (! in_array($mediaType, MediaType::filters(), true)) {
            return $this;
        }

        return $this->where('media_type', $mediaType);
    }

    public function filterByAccount(string $accountId): self
    {
        if ($accountId === 'all') {
            return $this;
        }

        return $this->where('social_account_id', (int) $accountId);
    }

    public function withoutClientsForUser(int $userId): self
    {
        return $this->whereDoesntHave('campaigns', fn (Builder $builder): Builder => $builder
            ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId)));
    }

    public function forClientOwnedByUser(int $clientId, int $userId): self
    {
        return $this->whereHas('campaigns', fn (Builder $builder): Builder => $builder
            ->where('campaigns.client_id', $clientId)
            ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId)));
    }

    public function filterByClient(string $clientId, int $userId): self
    {
        if ($clientId === 'without_clients') {
            return $this->withoutClientsForUser($userId);
        }

        if ($clientId === 'all') {
            return $this;
        }

        return $this->forClientOwnedByUser((int) $clientId, $userId);
    }

    public function publishedFrom(?string $start): self
    {
        if (filled($start)) {
            return $this->whereDate('published_at', '>=', $start);
        }

        return $this;
    }

    public function publishedUntil(?string $end): self
    {
        if (filled($end)) {
            return $this->whereDate('published_at', '<=', $end);
        }

        return $this;
    }

    public function sortForGallery(string $field, string $direction): self
    {
        return $this->orderBy($field, $direction)
            ->orderByDesc('id');
    }

    public function withOwnedCampaignsForUser(int $userId): self
    {
        return $this->with([
            'campaigns' => fn ($builder) => $builder
                ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId))
                ->with('client')
                ->withCount('instagramMedia')
                ->orderBy('name'),
        ]);
    }

    public function accountAverageMetricsForRecentDays(int $socialAccountId, int $days = 90): array
    {
        $averages = $this->where('social_account_id', $socialAccountId)
            ->where('published_at', '>=', now()->subDays($days))
            ->selectRaw('
                AVG(like_count) as avg_likes,
                AVG(comments_count) as avg_comments,
                AVG(reach) as avg_reach,
                AVG(engagement_rate) as avg_engagement
            ')
            ->first();

        return [
            'likes' => round((float) ($averages?->getAttribute('avg_likes') ?? 0), 2),
            'comments' => round((float) ($averages?->getAttribute('avg_comments') ?? 0), 2),
            'reach' => round((float) ($averages?->getAttribute('avg_reach') ?? 0), 2),
            'engagement_rate' => round((float) ($averages?->getAttribute('avg_engagement') ?? 0), 2),
        ];
    }

    public function forAnalyticsPeriod(AnalyticsPeriod $period): self
    {
        $periodStart = $period->startsAt();

        if ($periodStart === null) {
            return $this;
        }

        return $this->where('published_at', '>=', $periodStart);
    }

    public function topPerforming(AnalyticsTopContentSort $sort): self
    {
        return $this->orderByDesc($sort->metricColumn())
            ->orderByDesc('engagement_rate')
            ->orderByDesc('id');
    }

    public function engagementTrend(AnalyticsPeriod $period): Collection
    {
        $granularity = match ($period) {
            AnalyticsPeriod::SevenDays, AnalyticsPeriod::ThirtyDays => 'day',
            AnalyticsPeriod::NinetyDays => 'week',
            AnalyticsPeriod::AllTime => 'month',
        };

        return $this->get(['published_at', 'engagement_rate'])
            ->groupBy(function ($media) use ($granularity): string {
                $publishedAt = CarbonImmutable::parse($media->published_at);

                return match ($granularity) {
                    'day' => $publishedAt->startOfDay()->toDateString(),
                    'week' => $publishedAt->startOfWeek(CarbonInterface::MONDAY)->toDateString(),
                    'month' => $publishedAt->startOfMonth()->toDateString(),
                };
            })
            ->sortKeys()
            ->map(function (Collection $bucket, string $date): array {
                $average = round((float) $bucket->avg(fn ($media): float => (float) $media->engagement_rate), 2);
                $label = CarbonImmutable::parse($date)->isoFormat('MMM D, YYYY');

                return [
                    'date' => $date,
                    'label' => $label,
                    'value' => $average,
                ];
            })
            ->values();
    }

    public function contentTypeBreakdown(): Collection
    {
        $rows = $this->selectRaw('media_type, COUNT(*) as total_count, AVG(engagement_rate) as average_engagement_rate, AVG(reach) as average_reach')
            ->groupBy('media_type')
            ->get();

        $aggregates = $rows->mapWithKeys(function ($row): array {
            $mediaType = $row->media_type instanceof MediaType
                ? $row->media_type
                : MediaType::from((string) $row->media_type);

            return [
                $mediaType->value => [
                    'count' => (int) $row->total_count,
                    'average_engagement_rate' => round((float) $row->average_engagement_rate, 2),
                    'average_reach' => (int) round((float) $row->average_reach),
                ],
            ];
        });

        return collect(MediaType::cases())->mapWithKeys(fn (MediaType $mediaType): array => [
            $mediaType->value => $aggregates->get($mediaType->value, [
                'count' => 0,
                'average_engagement_rate' => 0.0,
                'average_reach' => 0,
            ]),
        ]);
    }
}
