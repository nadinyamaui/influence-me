<?php

namespace App\Livewire\Analytics;

use App\Enums\AnalyticsPeriod;
use App\Enums\MediaType;
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

    public function mount(): void
    {
        $this->period = AnalyticsPeriod::default()->value;
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

    public function render()
    {
        $period = AnalyticsPeriod::tryFrom($this->period) ?? AnalyticsPeriod::default();

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
        $chart = $this->audienceGrowthChart($period);

        return view('pages.analytics.index', [
            'accounts' => $accounts,
            'periodOptions' => AnalyticsPeriod::options(),
            'chart' => $chart,
            'summary' => [
                'total_followers' => $totalFollowers,
                'followers_change' => $followersChange,
                'total_posts' => $totalPosts,
                'post_breakdown' => $this->postBreakdown($media),
                'average_engagement_rate' => $averageEngagementRate,
                'total_reach' => $totalReach,
            ],
            'formatted' => [
                'total_followers' => number_format($totalFollowers),
                'total_reach' => $this->formatCompactNumber($totalReach),
                'average_engagement_rate' => number_format($averageEngagementRate, 2).'%',
                'followers_change' => $this->formatFollowersChange($followersChange),
            ],
        ])->layout('layouts.app', [
            'title' => __('Analytics'),
        ]);
    }

    private function postBreakdown(Collection $media): array
    {
        return [
            'posts' => $media->where('media_type', MediaType::Post)->count(),
            'reels' => $media->where('media_type', MediaType::Reel)->count(),
            'stories' => $media->where('media_type', MediaType::Story)->count(),
        ];
    }

    private function followersChange(AnalyticsPeriod $period): ?int
    {
        if ($period === AnalyticsPeriod::AllTime) {
            return null;
        }

        $currentTotal = (int) FollowerSnapshot::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->forAnalyticsPeriod($period)
            ->orderedByRecordedAt()
            ->get()
            ->groupBy(fn (FollowerSnapshot $snapshot): string => $snapshot->recorded_at->toDateString())
            ->map(fn (Collection $daySnapshots): int => (int) $daySnapshots->sum('followers_count'))
            ->last();

        if ($currentTotal === 0) {
            return null;
        }

        $previousPeriod = $this->previousPeriod($period);
        if ($previousPeriod === null) {
            return null;
        }

        $previousTotal = (int) FollowerSnapshot::query()
            ->forUser((int) Auth::id())
            ->filterByAccount($this->accountId)
            ->forAnalyticsPeriod($previousPeriod)
            ->orderedByRecordedAt()
            ->get()
            ->groupBy(fn (FollowerSnapshot $snapshot): string => $snapshot->recorded_at->toDateString())
            ->map(fn (Collection $daySnapshots): int => (int) $daySnapshots->sum('followers_count'))
            ->last();

        if ($previousTotal === 0) {
            return null;
        }

        return $currentTotal - $previousTotal;
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

    private function previousPeriod(AnalyticsPeriod $period): ?AnalyticsPeriod
    {
        return match ($period) {
            AnalyticsPeriod::SevenDays => AnalyticsPeriod::ThirtyDays,
            AnalyticsPeriod::ThirtyDays => AnalyticsPeriod::NinetyDays,
            AnalyticsPeriod::NinetyDays, AnalyticsPeriod::AllTime => null,
        };
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
}
