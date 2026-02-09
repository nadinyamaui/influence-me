# 022 - Sync Instagram Media Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #004, #020

## Description

Create a queued job that fetches all media (posts, reels) from the Instagram Graph API and creates/updates `InstagramMedia` records in the database. Handles pagination to fetch the complete media history.

## Implementation

### Create `App\Jobs\SyncInstagramMedia`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3
- Timeout: 300 seconds (5 min, since pagination can be slow)

### `handle()` Logic
1. Instantiate `InstagramGraphService` with the account
2. Loop through paginated `getMedia()` calls using cursor
3. For each media item, `updateOrCreate` by `instagram_media_id`:
   - Map `media_type` string from API to `MediaType` enum
   - Store: caption, permalink, media_url, thumbnail_url, published_at (from timestamp), like_count, comments_count
4. Continue until no more pages
5. Optionally: delete local media records that no longer exist on Instagram (soft approach: don't delete, just stop updating)

### Mapping
Instagram API `media_type` values → `MediaType` enum:
- `IMAGE` or `CAROUSEL_ALBUM` → `MediaType::Post`
- `VIDEO` → check if reel or regular video → `MediaType::Reel` or `MediaType::Post`

### Idempotency
- Uses `updateOrCreate` so running multiple times is safe
- New media is created, existing media is updated

## Files to Create
- `app/Jobs/SyncInstagramMedia.php`

## Acceptance Criteria
- [ ] Job fetches all media pages via pagination
- [ ] New media records created, existing ones updated
- [ ] Media types correctly mapped to enums
- [ ] Job is idempotent (safe to re-run)
- [ ] Timeout configured for large accounts
- [ ] Feature test verifies media sync (mocked HTTP)
