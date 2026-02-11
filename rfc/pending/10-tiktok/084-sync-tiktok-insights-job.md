# 084 - Sync TikTok Media Insights Job

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #081, #083

## Description

Create a queued job that updates insight metrics for synced TikTok media.

## Implementation

### Create Job
- `App\Jobs\SyncTikTokInsights`
- Calls `TikTokSyncService::syncMediaInsights()`

### Metrics
Persist views, likes, comments, shares, saves, and reach when available.

## Files to Create
- `app/Jobs/SyncTikTokInsights.php`

## Acceptance Criteria
- [ ] Insights update existing media rows
- [ ] Missing media/empty payloads handled safely
- [ ] Tests verify metric mapping and update behavior
