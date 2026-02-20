# 081 - TikTok Sync Service Class

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #004, #005, #080

## Description

Create a service layer that orchestrates TikTok sync workflows and shared-model persistence for accounts, media, insights, and demographics.

## Implementation

### Create Service
- `App\Services\TikTokSyncService`
- Methods:
  - `syncProfile(SocialAccount $account)`
  - `syncMedia(SocialAccount $account)`
  - `syncMediaInsights(SocialAccount $account)`
  - `syncDemographics(SocialAccount $account)`
  - `refreshAccessToken(SocialAccount $account)`

### Persistence Rules
- Upsert by platform IDs
- Enforce `social_network = tiktok` guard before sync execution
- Update `sync_status`, `last_synced_at`, and `last_sync_error` consistently

## Files to Create
- `app/Services/TikTokSyncService.php`

## Acceptance Criteria
- [ ] Service owns business workflow logic
- [ ] Upsert and status transitions are consistent
- [ ] Typed errors bubble up for jobs/controllers
- [ ] Unit tests cover success and failure paths
