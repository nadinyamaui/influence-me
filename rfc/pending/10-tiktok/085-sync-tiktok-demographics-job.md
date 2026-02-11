# 085 - Sync TikTok Audience Demographics Job

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #081

## Description

Create a queued job that captures TikTok audience demographic snapshots.

## Implementation

### Create Job
- `App\Jobs\SyncTikTokDemographics`
- Calls `TikTokSyncService::syncDemographics()`

### Snapshot Rules
- Store records per demographic type and captured timestamp

## Files to Create
- `app/Jobs/SyncTikTokDemographics.php`

## Acceptance Criteria
- [ ] Demographic snapshots are persisted by type
- [ ] Job is idempotent for repeated runs
- [ ] Tests cover empty and populated payloads
