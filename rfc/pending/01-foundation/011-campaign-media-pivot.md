# 011 - CampaignMedia Pivot Configuration

**Labels:** `feature`, `foundation`, `campaigns`
**Depends on:** #001, #004, #006

## Description

This issue ensures the `campaign_media` pivot table is properly configured in both the `Client` and `InstagramMedia` models. No new model file is needed — the pivot is accessed through `belongsToMany` on both sides.

## Implementation

### Client Model (`app/Models/Client.php`)
Ensure the relationship exists:
```php
public function instagramMedia(): BelongsToMany
{
    return $this->belongsToMany(InstagramMedia::class, 'campaign_media')
        ->withPivot('campaign_name', 'notes')
        ->withTimestamps();
}
```

### InstagramMedia Model (`app/Models/InstagramMedia.php`)
Ensure the relationship exists:
```php
public function clients(): BelongsToMany
{
    return $this->belongsToMany(Client::class, 'campaign_media')
        ->withPivot('campaign_name', 'notes')
        ->withTimestamps();
}
```

## Files to Modify
- `app/Models/Client.php` (verify/add relationship)
- `app/Models/InstagramMedia.php` (verify/add relationship)

## Acceptance Criteria
- [ ] Both sides of the many-to-many relationship work
- [ ] Pivot data (`campaign_name`, `notes`) is accessible
- [ ] Timestamps on pivot are tracked
- [ ] Feature tests verify attaching/detaching media to clients with pivot data
