# 078 - TikTokAudienceDemographic Model and Factory

**Labels:** `feature`, `tiktok`, `backend`
**Depends on:** #002, #076

## Description

Create `TikTokAudienceDemographic` model and migration to store audience distribution snapshots by age, gender, city, and country.

## Implementation

### Migration
Create `tiktok_audience_demographics` with:
- `id`
- `tiktok_account_id` (foreign key)
- `type` enum (`age`, `gender`, `city`, `country`)
- `label`
- `value`
- `captured_at`
- timestamps

### Model + Factory
- `App\Models\TikTokAudienceDemographic`
- Belongs to `TikTokAccount`
- Factory for each demographic type

## Files to Create
- `database/migrations/*_create_tiktok_audience_demographics_table.php`
- `app/Models/TikTokAudienceDemographic.php`
- `database/factories/TikTokAudienceDemographicFactory.php`

## Acceptance Criteria
- [ ] Migration supports time-series demographic snapshots
- [ ] Model relationship and casts are defined
- [ ] Factory supports all demographic types
- [ ] Tests verify type constraints and ownership boundaries
