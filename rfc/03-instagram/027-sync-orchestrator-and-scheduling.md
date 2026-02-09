# 027 - Sync Orchestrator Job and Scheduled Tasks

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #021, #022, #023, #024, #025, #026

## Description

Create a master orchestrator job that dispatches all individual sync jobs for an Instagram account, and configure the Laravel scheduler to run syncs automatically.

## Implementation

### Create `App\Jobs\SyncAllInstagramData`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`

### `handle()` Logic
1. Update account `sync_status` to `Syncing`
2. Dispatch jobs in sequence using a job chain:
   ```php
   Bus::chain([
       new SyncInstagramProfile($account),
       new SyncInstagramMedia($account),
       new SyncMediaInsights($account),
       new SyncInstagramStories($account),
       new SyncAudienceDemographics($account),
   ])->onQueue('instagram-sync')
     ->catch(function (Throwable $e) use ($account) {
         $account->update([
             'sync_status' => SyncStatus::Failed,
             'last_sync_error' => $e->getMessage(),
         ]);
     })
     ->dispatch();
   ```
3. After chain completes successfully: update `sync_status` to `Idle`, set `last_synced_at` to now, clear `last_sync_error`

### Schedule Configuration in `routes/console.php`

```php
use App\Models\InstagramAccount;
use App\Jobs\SyncAllInstagramData;
use App\Jobs\RefreshInstagramToken;
use App\Jobs\SyncInstagramProfile;
use App\Jobs\SyncMediaInsights;

// Full sync every 6 hours
Schedule::call(function () {
    InstagramAccount::each(fn ($account) => SyncAllInstagramData::dispatch($account));
})->everySixHours()->name('sync-all-instagram');

// Profile + insights refresh every hour
Schedule::call(function () {
    InstagramAccount::each(function ($account) {
        SyncInstagramProfile::dispatch($account);
        SyncMediaInsights::dispatch($account);
    });
})->hourly()->name('refresh-instagram-insights');

// Token refresh daily for tokens expiring within 7 days
Schedule::call(function () {
    InstagramAccount::where('token_expires_at', '<=', now()->addDays(7))
        ->each(fn ($account) => RefreshInstagramToken::dispatch($account));
})->daily()->name('refresh-instagram-tokens');
```

### Horizon Queue Configuration
Add `instagram-sync` queue to Horizon config (`config/horizon.php`) if not already present.

## Files to Create
- `app/Jobs/SyncAllInstagramData.php`

## Files to Modify
- `routes/console.php` — add scheduled tasks
- `config/horizon.php` — add queue worker for `instagram-sync`

## Acceptance Criteria
- [ ] Orchestrator dispatches all sync jobs in correct order
- [ ] Failed chain updates account status
- [ ] Successful chain updates `last_synced_at` and clears errors
- [ ] Schedule runs full sync every 6 hours
- [ ] Schedule runs profile/insights refresh hourly
- [ ] Schedule runs token refresh daily for expiring tokens
- [ ] Horizon configured with `instagram-sync` queue
- [ ] Feature test verifies orchestrator dispatches correct jobs
