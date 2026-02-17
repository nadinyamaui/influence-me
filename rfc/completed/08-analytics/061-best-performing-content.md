# 061 - Best Performing Content Section

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #058

## Description

Add a "Best Performing Content" section to the analytics dashboard showing the top 5 posts by engagement rate within the selected time period.

## Implementation

### Data Query
```php
$topContent = InstagramMedia::query()
    ->whereHas('instagramAccount', fn ($q) => $q->where('user_id', auth()->id()))
    ->when($this->accountId, fn ($q) => $q->where('instagram_account_id', $this->accountId))
    ->when($this->period !== 'all', fn ($q) => $q->where('published_at', '>=', $this->periodStart()))
    ->orderByDesc('engagement_rate')
    ->take(5)
    ->get();
```

### UI
Horizontal card layout for each top post:
- Thumbnail (small square)
- Caption preview (first 60 chars)
- Media type badge
- Published date
- Key metrics: engagement rate %, likes, reach
- Link to content detail (#063)

### Sorting Option
Toggle between "Top by Engagement" and "Top by Reach" (button group).

## Files to Modify
- `resources/views/pages/analytics/index.blade.php` — add best performing section

## Acceptance Criteria
- [ ] Top 5 content displayed with thumbnails and metrics
- [ ] Sorting toggle between engagement and reach works
- [ ] Responds to time period and account filters
- [ ] Links to content detail
- [ ] Feature test verifies correct ordering
