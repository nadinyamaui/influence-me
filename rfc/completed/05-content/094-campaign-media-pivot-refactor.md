# 094 - Campaign-Media Pivot Refactor

**Labels:** `feature`, `content`, `campaigns`, `database`
**Depends on:** #093

## Description

Refactor content linking so `campaign_media` links Instagram media to campaigns directly.

## Implementation

### Redefine `campaign_media` pivot
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| campaign_id | foreignId | constrained, cascadeOnDelete |
| instagram_media_id | foreignId | constrained, cascadeOnDelete |
| notes | text, nullable | |
| timestamps | | |

Unique index:
- `[campaign_id, instagram_media_id]`

### Relationship updates
- `Campaign belongsToMany InstagramMedia`
- `InstagramMedia belongsToMany Campaign`

### Deprecation rule
- `campaign_name` is removed from pivot requirements.
- Campaign grouping and lookup must come from campaign records, not pivot text fields.

## Files to Create/Modify
- `database/migrations/xxxx_refactor_campaign_media_table.php`
- `app/Models/Campaign.php`
- `app/Models/InstagramMedia.php`

## Acceptance Criteria
- [ ] Pivot uses `campaign_id` + `instagram_media_id`
- [ ] Duplicate links are prevented by unique constraint
- [ ] Link and unlink workflows operate through campaign relationship
- [ ] Tests verify relationship behavior and duplicate prevention
