# 083 - Sync TikTok Media Job

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #081

## Description

Create a queued job that syncs TikTok videos for a linked TikTok `SocialAccount` with cursor pagination.

## Implementation

### Create Job
- `App\Jobs\SyncTikTokMedia`
- Calls `TikTokSyncService::syncMedia()`
- Handles paginated fetch and upsert

## Files to Create
- `app/Jobs/SyncTikTokMedia.php`

## Acceptance Criteria
- [ ] Media sync supports pagination
- [ ] Media records are created/updated correctly
- [ ] Job failure paths are test-covered
