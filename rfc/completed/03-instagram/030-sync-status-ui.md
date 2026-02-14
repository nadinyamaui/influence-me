# 030 - Manual Sync Trigger and Status UI

**Labels:** `feature`, `instagram`, `ui`
**Depends on:** #027, #028

## Description

Add a "Sync Now" button on the Instagram Accounts page that manually triggers a full data sync for a specific account. Show real-time sync progress using Livewire polling.

## Implementation

### Livewire Actions on Accounts Page

**`syncNow(InstagramAccount $account)` action:**
1. Verify user owns the account
2. Check account is not already syncing
3. Dispatch `SyncAllInstagramData` job
4. Update account `sync_status` to `Syncing` immediately for UI feedback

### Real-Time Status Updates
Use Livewire polling to refresh the account status every 5 seconds while a sync is in progress:

```blade
@if($account->sync_status === SyncStatus::Syncing)
    <div wire:poll.5s>
        <!-- Spinner + "Syncing..." text -->
    </div>
@endif
```

Stop polling once status is `Idle` or `Failed`.

### UI Elements per Account Card
- **Sync Now** button (disabled while syncing)
- **Status badge:**
  - Idle: green dot + "Up to date"
  - Syncing: yellow spinner + "Syncing..."
  - Failed: red dot + "Sync failed" + expandable error message
- **Last synced:** relative timestamp ("2 hours ago", "Never")
- **Token warning:** if `token_expires_at` is within 7 days, show amber warning with "Re-authenticate" link

### Error Display
When `sync_status = failed`, show `last_sync_error` in a collapsible section below the account card.

## Files to Modify
- `resources/views/pages/instagram-accounts/index.blade.php` — add sync button, status, polling

## Acceptance Criteria
- [ ] "Sync Now" button dispatches sync job
- [ ] Button disabled while sync in progress
- [ ] Polling updates status in real-time
- [ ] Polling stops when sync completes
- [ ] Error messages displayed for failed syncs
- [ ] Last synced timestamp shows correctly
- [ ] Feature test verifies sync dispatch
