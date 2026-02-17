<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\InstagramMedia;

class ClientAnalyticsService
{
    public function build(Client $client): array
    {
        $linkedMedia = InstagramMedia::query()
            ->forClient($client->id)
            ->distinctMediaRows()
            ->forAnalyticsSummary()
            ->withCampaignsForClient($client->id)
            ->publishedChronologically()
            ->get();

        $totalPosts = $linkedMedia->count();
        $averageEngagementRate = $totalPosts > 0
            ? round((float) $linkedMedia->avg(fn (InstagramMedia $media): float => (float) $media->engagement_rate), 2)
            : 0.0;

        $campaignBreakdown = $client->campaigns()
            ->withInstagramMediaAnalyticsMetrics()
            ->orderedByName()
            ->get(['id', 'name'])
            ->map(function ($campaign): array {
                $media = collect($campaign->instagramMedia->all());

                return [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'posts' => $media->count(),
                    'reach' => (int) $media->sum('reach'),
                    'average_engagement_rate' => $media->isEmpty()
                        ? 0.0
                        : round((float) $media->avg(fn ($item): float => (float) $item->engagement_rate), 2),
                ];
            })
            ->filter(fn (array $item): bool => $item['posts'] > 0)
            ->values();

        $trendMedia = $linkedMedia
            ->filter(fn (InstagramMedia $media): bool => $media->published_at !== null)
            ->values();

        $accountAverageEngagementRate = round((float) (InstagramMedia::query()
            ->forUser($client->user_id)
            ->avg('engagement_rate') ?? 0), 2);

        $comparisonMax = max($averageEngagementRate, $accountAverageEngagementRate, 0.01);

        return [
            'has_linked_content' => $totalPosts > 0,
            'summary' => [
                'total_linked_posts' => $totalPosts,
                'total_reach' => (int) $linkedMedia->sum('reach'),
                'total_impressions' => (int) $linkedMedia->sum('impressions'),
                'average_engagement_rate' => $averageEngagementRate,
            ],
            'trend' => [
                'labels' => $trendMedia
                    ->map(fn (InstagramMedia $media): string => $media->published_at?->format('M j, Y') ?? 'Unknown date')
                    ->all(),
                'values' => $trendMedia
                    ->map(fn (InstagramMedia $media): float => round((float) $media->engagement_rate, 2))
                    ->all(),
            ],
            'campaign_breakdown' => $campaignBreakdown->all(),
            'comparison' => [
                'client_average_engagement_rate' => $averageEngagementRate,
                'account_average_engagement_rate' => $accountAverageEngagementRate,
                'client_average_percentage' => round(($averageEngagementRate / $comparisonMax) * 100, 1),
                'account_average_percentage' => round(($accountAverageEngagementRate / $comparisonMax) * 100, 1),
            ],
        ];
    }

    public function empty(): array
    {
        return [
            'has_linked_content' => false,
            'summary' => [
                'total_linked_posts' => 0,
                'total_reach' => 0,
                'total_impressions' => 0,
                'average_engagement_rate' => 0.0,
            ],
            'trend' => [
                'labels' => [],
                'values' => [],
            ],
            'campaign_breakdown' => [],
            'comparison' => [
                'client_average_engagement_rate' => 0.0,
                'account_average_engagement_rate' => 0.0,
                'client_average_percentage' => 0.0,
                'account_average_percentage' => 0.0,
            ],
        ];
    }
}
