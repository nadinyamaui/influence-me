# 004 - InstagramMedia Model and Factory

**Labels:** `feature`, `foundation`, `instagram`
**Depends on:** #001, #002, #003

## Description

Create the `InstagramMedia` Eloquent model with relationships, casts, and factory.

## Model: `App\Models\InstagramMedia`

### Fillable
`instagram_account_id`, `instagram_media_id`, `media_type`, `caption`, `permalink`, `media_url`, `thumbnail_url`, `published_at`, `like_count`, `comments_count`, `saved_count`, `shares_count`, `reach`, `impressions`, `engagement_rate`

### Casts
```php
protected function casts(): array
{
    return [
        'media_type' => MediaType::class,
        'published_at' => 'datetime',
        'engagement_rate' => 'decimal:2',
    ];
}
```

### Relationships
- `belongsTo(InstagramAccount::class)` - the account this media belongs to
- `belongsToMany(Client::class, 'campaign_media')->withPivot('campaign_name', 'notes')->withTimestamps()` - linked clients

### Factory States
- Default: generates realistic post data
- `post()`: media_type = Post
- `reel()`: media_type = Reel
- `story()`: media_type = Story
- `highEngagement()`: high like/comment counts

## Files to Create
- `app/Models/InstagramMedia.php`
- `database/factories/InstagramMediaFactory.php`

## Acceptance Criteria
- [ ] Model created with all fillable fields and casts
- [ ] Relationships defined with return type hints
- [ ] Factory produces valid model instances with all states
- [ ] Tests verify factory and relationships
