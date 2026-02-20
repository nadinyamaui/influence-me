# 066 - Client Portal Analytics Page

**Labels:** `feature`, `analytics`, `clients`, `ui`
**Depends on:** #035, #064, #065

## Description

Create an analytics page in the client portal where clients can view campaign performance and audience demographics for their linked content.

## Implementation

### Create Route
```php
Route::livewire('portal/analytics', 'portal.analytics.index')
    ->middleware(['auth:client'])
    ->name('portal.analytics.index');
```

### Create Livewire Page
`resources/views/pages/portal/analytics/index.blade.php`

### Page Content

**Campaign Performance Section:**
Same metrics as the client detail analytics tab (#064) but in the portal layout:
- Summary cards: linked posts, total reach, avg engagement
- Performance chart (engagement over time)
- Campaign breakdown table

**Audience Demographics Section:**
Show demographics for the Instagram accounts that have content linked to this client:
- Age distribution chart
- Gender breakdown chart
- Top cities chart
- Top countries chart

### Data Scoping
All data scoped through the authenticated ClientUser's client:
```php
$client = auth('client')->user()->client;
$linkedMedia = $client->instagramMedia()->with('instagramAccount')->get();

// Get demographics from linked accounts
$accountIds = $linkedMedia->pluck('instagram_account_id')->unique();
$demographics = AudienceDemographic::whereIn('instagram_account_id', $accountIds)->get();
```

### Update Portal Sidebar
Update `href="#"` for "Analytics" to `route('portal.analytics.index')`.

## Files to Create
- `resources/views/pages/portal/analytics/index.blade.php`

## Files to Modify
- `routes/portal.php` — add route
- `resources/views/layouts/portal/sidebar.blade.php` — update analytics link

## Acceptance Criteria
- [ ] Analytics page renders in portal layout
- [ ] Campaign metrics scoped to client's linked content
- [ ] Demographics charts render for linked accounts
- [ ] Data properly scoped (client cannot see other clients' data)
- [ ] Empty state when no linked content
- [ ] Feature test verifies data scoping

## Campaign Source Note

Campaign-first analytics sourcing requirements are defined in RFCs `064` and `097`, with TikTok dashboard expansion in RFC `092`.
