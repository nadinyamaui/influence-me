# 023 - Sync Media Insights Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #004, #020, #022

## Description

Create a queued job that fetches insights (reach, views, saved, shares) for each media item from the Instagram Graph API and updates the `InstagramMedia` records.

## Implementation

### Create `App\Jobs\SyncMediaInsights`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3
- Timeout: 600 seconds (10 min, many API calls)

### `handle()` Logic
1. Get all `InstagramMedia` records for this account (paginated in chunks of 50)
2. For each media item, call `getMediaInsights($mediaId)`
3. Update the media record with: `reach`, `impressions` (mapped from `views`), `saved_count`, `shares_count`
4. Calculate `engagement_rate`:
   ```
   engagement_rate = ((likes + comments + saved + shares) / reach) * 100
   ```
   If reach is 0, set engagement_rate to 0
### Optimization
- Only fetch insights for media published in the last 90 days (older content insights don't change)
- Use database chunking to avoid memory issues with large accounts
- Skip stories (they have different insight metrics, handled in #024)

## Files to Create
- `app/Jobs/SyncMediaInsights.php`

## Acceptance Criteria
- [ ] Job fetches insights for all recent media
- [ ] Metrics updated correctly on `InstagramMedia` records
- [ ] Engagement rate calculated correctly
- [ ] Only recent media (90 days) processed for efficiency
- [ ] Feature test verifies insights sync (mocked HTTP)
