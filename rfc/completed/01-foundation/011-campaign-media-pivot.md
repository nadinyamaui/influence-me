# 011 - CampaignMedia Link Configuration

**Labels:** `feature`, `foundation`, `campaigns`
**Depends on:** #001, #004, #006

## Description

Configure `campaign_media` as the campaign-scoped, polymorphic content-link table. Links must target campaign entities and platform media records, not free-text metadata.

## Implementation

### `campaign_media` table contract
- `campaign_id`
- `platform`
- `linkable_type`
- `linkable_id`
- `notes`
- timestamps

Uniqueness:
- Composite unique key on `[campaign_id, linkable_type, linkable_id]`

### Campaign link model (`app/Models/CampaignMedia.php`)
Ensure the model exposes:
- `belongsTo(Campaign::class)`
- `morphTo('linkable')`
- enum cast for `platform` via `PlatformType`

### Campaign model (`app/Models/Campaign.php`)
Ensure relationship exists:
```php
public function contentLinks(): HasMany
{
    return $this->hasMany(CampaignMedia::class);
}
```

### Platform media models
Each platform media model must expose a reverse polymorphic relationship to campaign links (for example on `InstagramMedia`, `TikTokMedia`, and future platform media models).

## Files to Modify
- `app/Models/Campaign.php`
- `app/Models/InstagramMedia.php`
- `app/Models/TikTokMedia.php` (when introduced)
- `app/Models/CampaignMedia.php`

## Acceptance Criteria
- [ ] Campaign-content links are stored through polymorphic rows in `campaign_media`
- [ ] Duplicate links are prevented by unique constraint
- [ ] Link rows include platform context via enum-backed `platform`
- [ ] Feature tests verify attach/detach and ownership enforcement
