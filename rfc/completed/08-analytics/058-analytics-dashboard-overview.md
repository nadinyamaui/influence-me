# 058 - Analytics Dashboard Overview Cards

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #004, #013

## Description

Create the analytics dashboard at `/analytics` with overview summary cards. This is the first piece of the analytics page — charts are added in subsequent issues.

## Implementation

### Create Route
```php
Route::livewire('analytics', 'analytics.index')
    ->middleware(['auth'])
    ->name('analytics.index');
```

### Create Livewire Page
`resources/views/pages/analytics/index.blade.php`

### Page Content

**Header:**
- "Analytics" title
- Time period selector: 7 days, 30 days, 90 days, All Time (Flux UI button group)
- Account filter: All accounts or specific account (dropdown, if multiple)
- Apply period/account filters in the Livewire component query builder (not in Blade)

**Overview Cards (4-column grid):**

1. **Total Followers**
   - Sum of `followers_count` across all (or selected) Instagram accounts
   - Change indicator: +/- vs previous period (if data available)

2. **Total Posts**
   - Count of `InstagramMedia` within the selected period
   - Breakdown: X posts, Y reels, Z stories

3. **Average Engagement Rate**
   - Average `engagement_rate` across all media in the period
   - Formatted as percentage

4. **Total Reach**
   - Sum of `reach` across all media in the period
   - Formatted with K/M suffix for large numbers

**Placeholder Sections:**
Below the cards, add placeholder divs for charts (implemented in #059-#062):
- Audience Growth Chart area
- Engagement Trend Chart area
- Best Performing Content area
- Content Type Breakdown area

### Query Optimization
Use eager loading and aggregate queries:
```php
$media = InstagramMedia::query()
    ->whereHas('instagramAccount', fn ($q) => $q->where('user_id', auth()->id()))
    ->when($this->accountId, fn ($q) => $q->where('instagram_account_id', $this->accountId))
    ->when($this->period !== 'all', fn ($q) => $q->where('published_at', '>=', $this->periodStart()))
    ->get();
```

### Update Sidebar
Update sidebar `href="#"` for "Analytics" to `route('analytics.index')`.

## Files to Create
- `resources/views/pages/analytics/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update analytics link

## Acceptance Criteria
- [ ] Page renders at `/analytics`
- [ ] Overview cards show correct aggregate metrics
- [ ] Time period selector filters data correctly
- [ ] Account filter works
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Numbers formatted nicely (K/M suffixes, percentages)
- [ ] Placeholder sections ready for charts
- [ ] Feature test verifies metric calculations
