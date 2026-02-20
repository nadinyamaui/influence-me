# 090 - TikTok Manual Sync Trigger and Status UI

**Labels:** `feature`, `tiktok`, `ui`, `backend`
**Depends on:** #087, #088

## Description

Add manual sync controls and detailed sync status UI for TikTok accounts within the shared connected-accounts UI.

## Implementation

### Manual Sync
- Add per-TikTok-account action to dispatch sync orchestrator/profile sync job

### Status Display
- Show `idle`, `syncing`, `failed` states
- Show `last_synced_at` and `last_sync_error`
- Add loading and disabled states during active sync

## Files to Modify
- existing shared accounts page/view
- `app/Jobs/SyncAllTikTokData.php` (if needed for scoped dispatch)

## Acceptance Criteria
- [ ] Manual sync action dispatches expected jobs
- [ ] Status updates are visible and accurate
- [ ] Failed sync includes clear error messaging
- [ ] Feature tests cover trigger and state rendering
