# 024 - Sync Instagram Stories Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #004, #020

## Description

Create a queued job that fetches currently active stories from the Instagram Graph API and creates/updates `InstagramMedia` records with `media_type = Story`.

## Implementation

### Create `App\Jobs\SyncInstagramStories`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3

### `handle()` Logic
1. Instantiate `InstagramGraphService` with the account
2. Call `getStories()` to get active stories
3. For each story, `updateOrCreate` by `instagram_media_id`:
   - Set `media_type` to `MediaType::Story`
   - Store: caption, permalink, media_url, thumbnail_url, published_at
4. Stories expire after 24h — old story records stay in the database but won't be updated

### Notes
- Stories have different insight metrics (replies, exits instead of likes/comments)
- Stories are ephemeral — this job captures a snapshot while they're live

## Files to Create
- `app/Jobs/SyncInstagramStories.php`

## Acceptance Criteria
- [ ] Job fetches active stories
- [ ] Stories created as `InstagramMedia` with type `Story`
- [ ] `updateOrCreate` prevents duplicates
- [ ] Feature test verifies story sync (mocked HTTP)
