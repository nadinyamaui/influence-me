# 064 - Campaign/Client Analytics Tab

**Labels:** `feature`, `analytics`, `clients`, `ui`
**Depends on:** #034, #041, #059

## Description

Populate the "Analytics" tab on the client detail page with aggregate performance metrics for all content linked to the client.

## Implementation

### Update Client Detail Page
Replace the Analytics tab placeholder on `/clients/{client}` with real analytics.

### Tab Content

**Summary Cards (2x2 grid):**
- Total Linked Posts: count
- Total Reach: sum of reach across linked content
- Total Impressions: sum of impressions
- Average Engagement Rate: avg across linked content

**Performance Over Time Chart:**
Line chart (Chart.js) showing engagement of linked content over time:
- X-axis: dates (of linked posts' publish dates)
- Y-axis: engagement rate
- Data points: each linked post

**Campaign Breakdown:**
If content is grouped by campaign names, show per-campaign stats:
| Campaign | Posts | Reach | Avg Engagement |
|----------|-------|-------|----------------|
| Summer Launch | 5 | 15,200 | 4.3% |
| Product Review | 3 | 8,100 | 3.8% |

**Comparison to Account Average:**
- Client's content avg engagement vs overall account average
- Visual bar comparison

### Data Query
```php
$linkedMedia = $client->instagramMedia()
    ->with('instagramAccount')
    ->get();

$totalReach = $linkedMedia->sum('reach');
$avgEngagement = $linkedMedia->avg('engagement_rate');
$campaignBreakdown = $linkedMedia->groupBy('pivot.campaign_name');
```

## Files to Modify
- `resources/views/pages/clients/show.blade.php` — populate Analytics tab

## Acceptance Criteria
- [ ] Summary cards show correct aggregate metrics
- [ ] Performance chart renders with linked content data
- [ ] Campaign breakdown table works
- [ ] Comparison to account average shown
- [ ] Empty state if no linked content
- [ ] Feature test verifies aggregation

## Campaign Source Note

Campaign-first analytics sourcing requirements are defined in RFC `099`.
