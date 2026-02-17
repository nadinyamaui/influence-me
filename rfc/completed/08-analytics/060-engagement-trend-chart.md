# 060 - Engagement Rate Trend Chart

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #058, #059

## Description

Add an engagement rate trend line chart to the analytics dashboard. Shows how average engagement rate changes over time.

## Implementation

### Chart Data
Aggregate engagement rate by day/week from `instagram_media` table:
```php
$engagementData = InstagramMedia::query()
    ->whereHas('instagramAccount', fn ($q) => $q->where('user_id', auth()->id()))
    ->when($this->accountId, fn ($q) => $q->where('instagram_account_id', $this->accountId))
    ->where('published_at', '>=', $this->periodStart())
    ->selectRaw('DATE(published_at) as date, AVG(engagement_rate) as avg_engagement')
    ->groupBy('date')
    ->orderBy('date')
    ->get();
```

### Chart Rendering
Same pattern as #059 using Chart.js + Alpine.js:
- Line chart with engagement rate percentage on Y-axis
- Dates on X-axis
- Different color from followers chart (e.g., emerald green)
- Show a horizontal reference line for the overall average

### Time Granularity
- 7 days: daily data points
- 30 days: daily data points
- 90 days: weekly averages
- All Time: monthly averages

## Files to Modify
- `resources/views/pages/analytics/index.blade.php` — add engagement chart

## Acceptance Criteria
- [ ] Line chart renders engagement rate over time
- [ ] Time granularity adjusts based on period
- [ ] Overall average reference line shown
- [ ] Responds to time period and account filters
- [ ] Feature test verifies data aggregation
