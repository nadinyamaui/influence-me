# 086 - TikTok Token Refresh Job

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #081

## Description

Create a queued job that refreshes expiring TikTok access tokens and stores updated expiry timestamps.

## Implementation

### Create Job
- `App\Jobs\RefreshTikTokToken`
- Calls `TikTokSyncService::refreshAccessToken()`

### Behavior
- Runs only for TikTok `SocialAccount` records expiring within a configurable threshold
- Logs and marks sync failure when refresh fails

## Files to Create
- `app/Jobs/RefreshTikTokToken.php`

## Acceptance Criteria
- [ ] Tokens refresh before expiration
- [ ] Expiration timestamps update correctly
- [ ] Failure path is logged and tested
