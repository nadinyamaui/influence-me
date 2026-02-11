# 082 - Sync TikTok Profile Job

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #081

## Description

Create a queued job to sync a single TikTok account profile.

## Implementation

### Create Job
- `App\Jobs\SyncTikTokProfile`
- Implements `ShouldQueue`
- Queue: `tiktok-sync`
- Backoff and retry policy aligned with Instagram sync jobs

### Handle Flow
- Call `TikTokSyncService::syncProfile()`
- Handle token expiration and API errors with typed exceptions

## Files to Create
- `app/Jobs/SyncTikTokProfile.php`

## Acceptance Criteria
- [ ] Job dispatches and processes successfully
- [ ] Failure handling updates account sync state
- [ ] Feature test covers success and typed failure behavior
