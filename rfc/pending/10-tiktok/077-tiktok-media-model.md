# 077 - TikTokMedia Model and Factory

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #002, #076

## Description

Create `TikTokMedia` model and migration to store synced TikTok videos and performance metrics.

## Implementation

### Migration
Create `tiktok_media` with:
- `id`
- `tiktok_account_id` (foreign key)
- `platform_media_id` (unique)
- `caption` nullable
- `media_type` enum aligned with existing media enum mapping
- `thumbnail_url` nullable
- `permalink`
- `published_at`
- `view_count` default 0
- `like_count` default 0
- `comment_count` default 0
- `share_count` default 0
- `save_count` default 0
- `reach_count` default 0
- timestamps

### Model + Factory
- `App\Models\TikTokMedia`
- Belongs to `TikTokAccount`
- Factory with realistic metric defaults

## Files to Create
- `database/migrations/*_create_tiktok_media_table.php`
- `app/Models/TikTokMedia.php`
- `database/factories/TikTokMediaFactory.php`

## Acceptance Criteria
- [ ] Migration and indexes support sync upserts
- [ ] Model relation to `TikTokAccount` exists
- [ ] Factory generates valid media rows
- [ ] Tests verify ownership-safe querying
