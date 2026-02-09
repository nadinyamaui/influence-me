# 062 - Content Type Breakdown Chart

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #058, #059

## Description

Add a pie/doughnut chart showing the distribution of content types (Posts vs Reels vs Stories) within the selected time period.

## Implementation

### Data Query
```php
$breakdown = InstagramMedia::query()
    ->whereHas('instagramAccount', fn ($q) => $q->where('user_id', auth()->id()))
    ->when($this->accountId, fn ($q) => $q->where('instagram_account_id', $this->accountId))
    ->when($this->period !== 'all', fn ($q) => $q->where('published_at', '>=', $this->periodStart()))
    ->selectRaw('media_type, COUNT(*) as count')
    ->groupBy('media_type')
    ->pluck('count', 'media_type');
```

### Chart Rendering
Doughnut chart using Chart.js + Alpine.js:
- Posts: blue
- Reels: purple
- Stories: amber
- Center text: total count
- Legend with counts and percentages

### Additional Stats Below Chart
For each content type, show:
- Count
- Average engagement rate
- Average reach

## Files to Modify
- `resources/views/pages/analytics/index.blade.php` — add breakdown chart

## Acceptance Criteria
- [ ] Doughnut chart renders content type distribution
- [ ] Colors distinguish between types
- [ ] Legend shows counts and percentages
- [ ] Per-type stats shown below chart
- [ ] Responds to time period and account filters
- [ ] Feature test verifies breakdown calculation
