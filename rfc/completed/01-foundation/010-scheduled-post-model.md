# 010 - ScheduledPost Model and Factory

**Labels:** `feature`, `foundation`, `content`
**Depends on:** #001, #002, #003, #006, #093

## Description

Create the `ScheduledPost` Eloquent model with relationships, casts, and factory. Update User model with relationship.

## Model: `App\Models\ScheduledPost`

### Mass Assignment
Use:
```php
protected $guarded = [];
```

Expected persisted attributes:
`user_id`, `client_id`, `campaign_id`, `instagram_account_id`, `title`, `description`, `media_type`, `scheduled_at`, `status`

### Casts
```php
protected function casts(): array
{
    return [
        'media_type' => MediaType::class,
        'status' => ScheduledPostStatus::class,
        'scheduled_at' => 'datetime',
    ];
}
```

### Relationships
- `belongsTo(User::class)`
- `belongsTo(Client::class)` - nullable
- `belongsTo(Campaign::class)` - nullable
- `belongsTo(InstagramAccount::class)`

### Factory States
- Default: planned post in the future
- `planned()`: status = Planned
- `published()`: status = Published
- `cancelled()`: status = Cancelled

### User Model Update
Add to `app/Models/User.php`:
```php
public function scheduledPosts(): HasMany
{
    return $this->hasMany(ScheduledPost::class);
}
```

## Files to Create/Modify
- `app/Models/ScheduledPost.php`
- `database/factories/ScheduledPostFactory.php`
- `app/Models/User.php` (add relationship)

## Acceptance Criteria
- [ ] Model created with `protected $guarded = [];` and required casts
- [ ] Relationships defined with return type hints
- [ ] Client relationship is nullable
- [ ] Campaign relationship is nullable
- [ ] `media_type` is cast to `MediaType`
- [ ] Factory produces valid instances with all states
- [ ] User model has `scheduledPosts()` relationship
- [ ] Tests verify factory and relationships
