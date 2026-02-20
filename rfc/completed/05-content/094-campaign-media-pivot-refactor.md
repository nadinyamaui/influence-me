# 094 - Campaign-Media Pivot Refactor

**Labels:** `feature`, `content`, `campaigns`, `database`
**Depends on:** #093

## Description

Refactor content linking so `campaign_media` links campaign entities to platform media records through a polymorphic relation.

## Implementation

### Redefine `campaign_media` pivot
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| campaign_id | foreignId | constrained, cascadeOnDelete |
| platform | string | enum-backed `PlatformType` |
| linkable_type | string | target media model class |
| linkable_id | unsignedBigInteger | target media model key |
| notes | text, nullable | |
| timestamps | | |

Unique index:
- `[campaign_id, linkable_type, linkable_id]`

Index:
- `[platform, linkable_type, linkable_id]`

### Relationship updates
- `Campaign hasMany CampaignMedia`
- `CampaignMedia morphTo linkable`
- Platform media models (`InstagramMedia`, `TikTokMedia`, and future platform models) expose reverse polymorphic links

### Deprecation rule
- Free-text pivot campaign metadata is not a source of truth.
- Campaign grouping and lookup must come from campaign records.

## Files to Create/Modify
- `database/migrations/xxxx_refactor_campaign_media_table.php`
- `app/Models/Campaign.php`
- `app/Models/CampaignMedia.php`
- platform media model files (Instagram/TikTok and future additions)

## Acceptance Criteria
- [ ] Pivot uses `campaign_id` + polymorphic target columns
- [ ] Duplicate links are prevented by unique constraint
- [ ] Link and unlink workflows operate through campaign polymorphic relationships
- [ ] Tests verify relationship behavior and duplicate prevention
