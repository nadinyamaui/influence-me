# 021 - Sync Instagram Profile Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #020

## Description

Create a queued job that syncs an Instagram account's profile data (username, bio, followers, following, media count, profile picture) from the Graph API to the local database.

## Implementation

### Create `App\Jobs\SyncInstagramProfile`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3
- Retry backoff: `[30, 60, 120]` seconds

### `handle()` Logic
1. Instantiate `InstagramGraphService` with the account
2. Call `getProfile()`
3. Update `InstagramAccount` with: `username`, `name`, `biography`, `profile_picture_url`, `followers_count`, `following_count`, `media_count`, `account_type`
4. No errors → done

### Error Handling
- `InstagramTokenExpiredException`: log warning, mark account `sync_status = failed`, set `last_sync_error`
- `InstagramApiException`: retry with backoff
- Any other exception: fail the job

## Files to Create
- `app/Jobs/SyncInstagramProfile.php`

## Acceptance Criteria
- [ ] Job implements `ShouldQueue`
- [ ] Profile data updated correctly in database
- [ ] Token expiry handled gracefully
- [ ] Retry logic works with backoff
- [ ] Feature test verifies data is saved (mocked HTTP)
