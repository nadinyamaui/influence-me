# 081 - TikTok Sync Service Class

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #076, #077, #078, #080

## Description

Create a service layer that orchestrates TikTok sync workflows and database persistence for accounts, media, insights, and demographics.

## Implementation

### Create Service
- `App\Services\TikTokSyncService`
- Methods:
  - `syncProfile(TikTokAccount $account)`
  - `syncMedia(TikTokAccount $account)`
  - `syncMediaInsights(TikTokAccount $account)`
  - `syncDemographics(TikTokAccount $account)`
  - `refreshAccessToken(TikTokAccount $account)`

### Persistence Rules
- Upsert by platform IDs
- Update `sync_status`, `last_synced_at`, and `last_sync_error` consistently

## Files to Create
- `app/Services/TikTokSyncService.php`

## Acceptance Criteria
- [ ] Service owns business workflow logic
- [ ] Upsert and status transitions are consistent
- [ ] Typed errors bubble up for jobs/controllers
- [ ] Unit tests cover success and failure paths
