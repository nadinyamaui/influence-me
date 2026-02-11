# 087 - TikTok Sync Orchestrator and Scheduled Tasks

**Labels:** `feature`, `tiktok`, `backend`, `infrastructure`
**Depends on:** #082, #083, #084, #085, #086

## Description

Create an orchestration job and scheduler entries for periodic TikTok sync operations.

## Implementation

### Orchestrator Job
- `App\Jobs\SyncAllTikTokData`
- For each connected account, chain profile/media/insights/demographics jobs

### Scheduler Cadence
- Full sync every 6 hours
- Profile + insights hourly
- Token refresh daily for expiring tokens

### Queue Configuration
- Add `tiktok-sync` queue lane and worker docs/config updates

## Files to Create
- `app/Jobs/SyncAllTikTokData.php`

## Files to Modify
- `routes/console.php`
- `config/horizon.php` (if used)

## Acceptance Criteria
- [ ] Orchestrator dispatches job chain in expected order
- [ ] Scheduler runs with required cadence
- [ ] Queue lane configured and documented
- [ ] Tests assert schedule and dispatch behavior
