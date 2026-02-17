<?php

namespace App\Livewire\Analytics;

use App\Enums\AnalyticsPeriod;
use App\Enums\AnalyticsTopContentSort;
use App\Enums\DemographicType;
use App\Enums\MediaType;
use App\Models\AudienceDemographic;
use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $period = '30_days';

    public string $accountId = 'all';

    public string $topContentSort = 'engagement';

    public function mount(): void
    {
        $this->period = AnalyticsPeriod::default()->value;
        $this->topContentSort = AnalyticsTopContentSort::default()->value;
    }

    public function updatedPeriod(string $value): void
    {
        if (! in_array($value, AnalyticsPeriod::values(), true)) {
            $this->period = AnalyticsPeriod::default()->value;
        }
    }

    public function updatedAccountId(string $value): void
    {
        if ($value !== 'all') {
            $accountId = (int) $value;
            $hasAccount = Auth::user()?->instagramAccounts()->whereKey($accountId)->exists() ?? false;

            if (! $hasAccount) {
                $this->accountId = 'all';
            }
        }
    }

    public function updatedTopContentSort(string $value): void
    {
        if (! in_array($value, AnalyticsTopContentSort::values(), true)) {
            $this->topContentSort = AnalyticsTopContentSort::default()->value;
        }
    }

    public function render()
    {
        $period = AnalyticsPeriod::tryFrom($this->period) ?? AnalyticsPeriod::default();
        $topContentSort = AnalyticsTopContentSort::tryFrom($this->topContentSort) ?? AnalyticsTopContentSort::default();

        $accounts = Auth::user()?->instagramAccounts()
            ->orderBy('username')
            ->get(['id', 'username']) ?? collect();

        $accountQuery = InstagramAccount::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId);

        $mediaQuery = InstagramMedia::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->forAnalyticsPeriod($period);

        $media = $mediaQuery->get([
            'id',
            'instagram_account_id',
            'media_type',
            'engagement_rate',
            'reach',
            'published_at',
        ]);

        $totalFollowers = (int) $accountQuery->sum('followers_count');
        $totalPosts = $media->count();
        $totalReach = (int) $media->sum('reach');
        $averageEngagementRate = $totalPosts > 0
            ? round((float) $media->avg(fn (InstagramMedia $item): float => (float) $item->engagement_rate), 2)
            : 0.0;

        $followersChange = $this->followersChange($period);
        $contentTypeStats = (clone $mediaQuery)->contentTypeBreakdown();
        $engagementTrendPoints = (clone $mediaQuery)->engagementTrend($period);
        $engagementTrendLabels = $engagementTrendPoints->pluck('label')->all();
        $engagementTrendValues = $engagementTrendPoints->pluck('value')->all();
        $contentTypeBreakdown = $this->contentTypeBreakdownChart($contentTypeStats, $totalPosts);
        $chart = $this->audienceGrowthChart($period);
        $demographics = $this->audienceDemographics();
        $topContent = InstagramMedia::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->forAnalyticsPeriod($period)
            ->topPerforming($topContentSort)
            ->take(5)
            ->get([
                'id',
                'media_type',
                'caption',
                'thumbnail_url',
                'media_url',
                'published_at',
                'engagement_rate',
                'like_count',
                'reach',
            ]);

        return view('pages.analytics.index', [
            'accounts' => $accounts,
            'periodOptions' => AnalyticsPeriod::options(),
            'topContentSortOptions' => AnalyticsTopContentSort::options(),
            'chart' => $chart,
            'topContent' => $topContent,
            'summary' => [
                'total_followers' => $totalFollowers,
                'followers_change' => $followersChange,
                'total_posts' => $totalPosts,
                'post_breakdown' => $this->postBreakdown($contentTypeStats),
                'average_engagement_rate' => $averageEngagementRate,
                'total_reach' => $totalReach,
            ],
            'formatted' => [
                'total_followers' => number_format($totalFollowers),
                'total_reach' => $this->formatCompactNumber($totalReach),
                'average_engagement_rate' => number_format($averageEngagementRate, 2).'%',
                'followers_change' => $this->formatFollowersChange($followersChange),
            ],
            'engagementTrend' => [
                'labels' => $engagementTrendLabels,
                'values' => $engagementTrendValues,
                'average' => $averageEngagementRate,
                'average_line' => array_fill(0, count($engagementTrendLabels), $averageEngagementRate),
            ],
            'contentTypeBreakdown' => $contentTypeBreakdown,
            'audienceDemographics' => $demographics,
        ])->layout('layouts.app', [
            'title' => __('Analytics'),
        ]);
    }

    private function postBreakdown(Collection $contentTypeStats): array
    {
        return [
            'posts' => (int) ($contentTypeStats->get(MediaType::Post->value)['count'] ?? 0),
            'reels' => (int) ($contentTypeStats->get(MediaType::Reel->value)['count'] ?? 0),
            'stories' => (int) ($contentTypeStats->get(MediaType::Story->value)['count'] ?? 0),
        ];
    }

    private function contentTypeBreakdownChart(Collection $contentTypeStats, int $totalPosts): array
    {
        $items = collect(MediaType::cases())
            ->map(function (MediaType $mediaType) use ($contentTypeStats, $totalPosts): array {
                $stats = $contentTypeStats->get($mediaType->value, [
                    'count' => 0,
                    'average_engagement_rate' => 0.0,
                    'average_reach' => 0,
                ]);
                $count = (int) ($stats['count'] ?? 0);
                $percentage = $totalPosts > 0 ? round(($count / $totalPosts) * 100, 1) : 0.0;

                return [
                    'key' => $mediaType->value,
                    'label' => $mediaType->pluralLabel(),
                    'count' => $count,
                    'percentage' => $percentage,
                    'average_engagement_rate' => round((float) ($stats['average_engagement_rate'] ?? 0), 2),
                    'average_reach' => (int) ($stats['average_reach'] ?? 0),
                    'color' => $mediaType->chartColor(),
                ];
            })
            ->values();

        return [
            'labels' => $items->pluck('label')->all(),
            'values' => $items->pluck('count')->all(),
            'colors' => $items->pluck('color')->all(),
            'total' => $totalPosts,
            'items' => $items->all(),
        ];
    }

    private function followersChange(AnalyticsPeriod $period): ?int
    {
        if ($period === AnalyticsPeriod::AllTime) {
            return null;
        }

        return null;
    }

    private function audienceGrowthChart(AnalyticsPeriod $period): array
    {
        $points = FollowerSnapshot::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->forAnalyticsPeriod($period)
            ->orderedByRecordedAt()
            ->get(['followers_count', 'recorded_at'])
            ->groupBy(fn (FollowerSnapshot $snapshot): string => $snapshot->recorded_at->toDateString())
            ->map(fn (Collection $daySnapshots): int => (int) $daySnapshots->sum('followers_count'));

        return [
            'labels' => $points->keys()->values()->all(),
            'data' => $points->values()->all(),
        ];
    }

    private function formatFollowersChange(?int $followersChange): string
    {
        if ($followersChange === null) {
            return 'No prior snapshot';
        }

        if ($followersChange === 0) {
            return 'No change';
        }

        return ($followersChange > 0 ? '+' : '').number_format($followersChange);
    }

    private function formatCompactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1).'M';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, 1).'K';
        }

        return number_format($value);
    }

    private function audienceDemographics(): array
    {
        $demographics = AudienceDemographic::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->get(['instagram_account_id', 'type', 'dimension', 'value'])
            ->groupBy(fn (AudienceDemographic $item): string => $item->type->value);
        $accountWeights = InstagramAccount::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->pluck('followers_count', 'id')
            ->mapWithKeys(fn ($followersCount, $id): array => [(int) $id => max((int) $followersCount, 1)]);

        $ageOrder = collect(['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+']);

        $ageRows = $demographics->get(DemographicType::Age->value, collect());
        $ageLookup = $this->weightedDemographicValues($ageRows, $accountWeights);
        $ageLabels = $ageOrder->all();
        $ageValues = $ageOrder
            ->map(fn (string $label): float => (float) ($ageLookup->get($label) ?? 0.0))
            ->all();

        $genderRows = $demographics->get(DemographicType::Gender->value, collect());
        $genderKeys = collect(['Male', 'Female', 'Other']);
        $genderLookup = $this->weightedDemographicValues($genderRows, $accountWeights);
        $genderLabels = $genderKeys->all();
        $genderValues = $genderKeys
            ->map(fn (string $label): float => (float) ($genderLookup->get($label) ?? 0.0))
            ->all();
        $genderTotal = round(array_sum($genderValues), 2);

        $cityRows = $demographics->get(DemographicType::City->value, collect());
        $city = $this->topDemographicRows($cityRows, $accountWeights);

        $countryRows = $demographics->get(DemographicType::Country->value, collect());
        $country = $this->topDemographicRows($countryRows, $accountWeights);

        return [
            'has_data' => $ageRows->isNotEmpty() || $genderRows->isNotEmpty() || $cityRows->isNotEmpty() || $countryRows->isNotEmpty(),
            'age' => [
                'labels' => $ageLabels,
                'values' => $ageValues,
            ],
            'gender' => [
                'labels' => $genderLabels,
                'values' => $genderValues,
                'colors' => ['#3b82f6', '#ec4899', '#9ca3af'],
                'total' => $genderTotal,
            ],
            'city' => $city,
            'country' => $country,
        ];
    }

    private function topDemographicRows(Collection $rows, Collection $accountWeights): array
    {
        $grouped = $this->weightedDemographicValues($rows, $accountWeights)
            ->sortDesc()
            ->take(10);

        return [
            'labels' => $grouped->keys()->values()->all(),
            'values' => $grouped->values()->all(),
        ];
    }

    private function weightedDemographicValues(Collection $rows, Collection $accountWeights): Collection
    {
        $typeAccountIds = $rows->pluck('instagram_account_id')
            ->map(fn ($accountId): int => (int) $accountId)
            ->unique()
            ->values()
            ->all();
        $scopedWeights = $accountWeights->only($typeAccountIds);

        if ($scopedWeights->isEmpty()) {
            $scopedWeights = $accountWeights;
        }

        return $rows
            ->groupBy('dimension')
            ->map(function (Collection $dimensionRows) use ($scopedWeights): float {
                $perAccount = $dimensionRows
                    ->groupBy('instagram_account_id')
                    ->map(fn (Collection $accountRows): float => (float) $accountRows->sum(fn (AudienceDemographic $item): float => (float) $item->value));
                $weightedSum = 0.0;
                $totalWeight = 0;

                foreach ($scopedWeights as $accountId => $weight) {
                    $value = (float) ($perAccount->get((int) $accountId) ?? 0.0);
                    $weightedSum += $value * (int) $weight;
                    $totalWeight += $weight;
                }

                return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
            });
    }
}
