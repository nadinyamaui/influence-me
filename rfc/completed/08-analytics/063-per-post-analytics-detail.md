# 063 - Per-Post Analytics Detail View

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #039

## Description

Enhance the content detail modal (#039) with analytics comparisons. When viewing a post's details, show how it performs relative to the account average.

## Implementation

### Enhancements to Content Detail Modal

**Comparison Metrics:**
For each metric, show the post's value AND a comparison to the account average:
- Likes: 245 (+32% above average)
- Comments: 18 (-5% below average)
- Reach: 3,400 (+15% above average)
- Engagement Rate: 4.2% (Account avg: 3.5%)

**Visual Indicators:**
- Green arrow up + percentage for above average
- Red arrow down + percentage for below average
- Gray dash for at average

### Account Average Calculation
```php
$averages = InstagramMedia::query()
    ->where('instagram_account_id', $media->instagram_account_id)
    ->where('published_at', '>=', now()->subDays(90))
    ->selectRaw('
        AVG(like_count) as avg_likes,
        AVG(comments_count) as avg_comments,
        AVG(reach) as avg_reach,
        AVG(engagement_rate) as avg_engagement
    ')
    ->first();
```

**Campaign Context:**
If the media is linked to a client, show:
- Client name with link
- Campaign name
- "Part of campaign with X other posts"

## Files to Modify
- `resources/views/pages/content/index.blade.php` — enhance detail modal with comparisons

## Acceptance Criteria
- [ ] Per-metric comparison to account average shown
- [ ] Visual indicators (up/down arrows) are clear
- [ ] Campaign context displayed when linked
- [ ] Averages calculated from last 90 days
- [ ] Feature test verifies comparison calculations
