# 076 - TikTokAccount Model, Factory, and Seeder

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #001, #002

## Description

Create a `TikTokAccount` domain model and migration for storing linked TikTok account credentials, metadata, and sync status.

## Implementation

### Migration
Create `tiktok_accounts` with:
- `id`
- `user_id` (foreign key)
- `platform_account_id` (unique)
- `username`
- `display_name`
- `avatar_url` nullable
- `followers_count` default 0
- `following_count` default 0
- `video_count` default 0
- `bio` nullable
- `access_token`
- `refresh_token` nullable
- `token_expires_at` nullable
- `sync_status` enum (`idle`, `syncing`, `failed`)
- `last_synced_at` nullable
- `last_sync_error` nullable
- timestamps

### Model + Factory + Seeder
- `App\Models\TikTokAccount`
- Relationship to `User`
- Factory and optional seeder sample data

## Files to Create
- `database/migrations/*_create_tiktok_accounts_table.php`
- `app/Models/TikTokAccount.php`
- `database/factories/TikTokAccountFactory.php`

## Acceptance Criteria
- [ ] Migration creates required columns and constraints
- [ ] Model relationships are defined
- [ ] Factory creates valid records
- [ ] Tests cover creation and ownership scoping
