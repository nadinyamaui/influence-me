# 037 - Client Portal Dashboard

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #035, #036

## Description

Replace the placeholder portal dashboard with a real summary page showing the client's engagement with the influencer. Displays key metrics and recent activity.

## Implementation

### Update Portal Dashboard
`resources/views/pages/portal/dashboard.blade.php` (convert to Livewire component)

### Route
```php
Route::livewire('portal/dashboard', 'portal.dashboard')
    ->middleware(['auth:client'])
    ->name('portal.dashboard');
```

### Dashboard Content

**Welcome Header:**
"Welcome back, {client name}" with the influencer's name/brand

**Summary Cards (2x2 grid):**
- Active Proposals: count of proposals with status Sent
- Pending Invoices: count + total amount of unpaid invoices
- Linked Content: total number of linked Instagram posts
- Total Reach: sum of reach across all linked content

**Recent Activity (list):**
- Last 5 proposals (with status badge)
- Last 5 invoices (with status badge and amount)

### Data Scoping
All queries scoped through the authenticated ClientUser's `client` relationship:
```php
$client = auth('client')->user()->client;
$proposals = $client->proposals()->latest()->take(5)->get();
$invoices = $client->invoices()->latest()->take(5)->get();
```

## Files to Modify
- `resources/views/pages/portal/dashboard.blade.php` — replace placeholder
- `routes/portal.php` — update route to Livewire

## Acceptance Criteria
- [ ] Dashboard shows correct summary metrics
- [ ] Data scoped to authenticated client only
- [ ] Recent proposals and invoices listed
- [ ] Links navigate to portal detail pages (placeholder for now)
- [ ] Feature test verifies data scoping and display
