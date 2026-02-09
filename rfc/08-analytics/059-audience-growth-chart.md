# 059 - Audience Growth Chart

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #058

## Description

Add a followers-over-time line chart to the analytics dashboard. Uses Chart.js rendered via Alpine.js with data prepared by Livewire.

## Implementation

### Install Chart.js
```bash
npm install chart.js
```

Import in `resources/js/app.js`:
```js
import Chart from 'chart.js/auto';
window.Chart = Chart;
```

### Chart Component
Add to the analytics dashboard page, replacing the placeholder.

**Data Source:**
Since Instagram Graph API doesn't provide historical follower data, we'll track it ourselves:
- Create a new migration: `follower_snapshots` table
  - `id`, `instagram_account_id`, `followers_count`, `recorded_at`, `timestamps`
- Add a new scheduled job that records daily follower counts
- Query snapshots for the chart

### Scheduled Job: `App\Jobs\RecordFollowerSnapshot`
Runs daily, records current follower count for each account.

Schedule in `routes/console.php`:
```php
Schedule::call(function () {
    InstagramAccount::each(fn ($account) =>
        FollowerSnapshot::create([
            'instagram_account_id' => $account->id,
            'followers_count' => $account->followers_count,
            'recorded_at' => now(),
        ])
    );
})->daily()->name('record-follower-snapshots');
```

### Chart Rendering
```blade
<div
    x-data="{ chart: null }"
    x-init="chart = new Chart($refs.canvas, {
        type: 'line',
        data: {
            labels: @js($chartLabels),
            datasets: [{
                label: 'Followers',
                data: @js($chartData),
                borderColor: '#6366f1',
                tension: 0.3,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: false } }
        }
    })"
>
    <canvas x-ref="canvas"></canvas>
</div>
```

### Migration
Create `database/migrations/xxxx_create_follower_snapshots_table.php`:
- `instagram_account_id` (foreignId, constrained)
- `followers_count` (unsignedInteger)
- `recorded_at` (timestamp)

### Model
Create `App\Models\FollowerSnapshot` with factory.

## Files to Create
- `database/migrations/xxxx_create_follower_snapshots_table.php`
- `app/Models/FollowerSnapshot.php`
- `database/factories/FollowerSnapshotFactory.php`

## Files to Modify
- `resources/js/app.js` — import Chart.js
- `resources/views/pages/analytics/index.blade.php` — add chart
- `routes/console.php` — add daily snapshot schedule
- `package.json` — add chart.js dependency

## Acceptance Criteria
- [ ] Chart.js installed and imported
- [ ] Line chart renders follower data over time
- [ ] Chart responds to time period selector
- [ ] Chart responds to account filter
- [ ] Daily snapshot job records follower counts
- [ ] Feature test verifies data preparation
