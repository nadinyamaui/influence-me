# 003 - InstagramAccount Model, Factory, and Seeder

**Labels:** `feature`, `foundation`, `instagram`
**Depends on:** #001, #002

## Description

Create the `InstagramAccount` Eloquent model with relationships, casts, factory, and seed data. Also update the `User` model to add the `hasMany` relationship.

## Model: `App\Models\InstagramAccount`

### Fillable
`user_id`, `instagram_user_id`, `username`, `name`, `biography`, `profile_picture_url`, `account_type`, `followers_count`, `following_count`, `media_count`, `access_token`, `token_expires_at`, `is_primary`, `last_synced_at`, `sync_status`, `last_sync_error`

### Casts
```php
protected function casts(): array
{
    return [
        'account_type' => AccountType::class,
        'sync_status' => SyncStatus::class,
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_primary' => 'boolean',
        'access_token' => 'encrypted',
    ];
}
```

### Relationships
- `belongsTo(User::class)` - the owning influencer
- `hasMany(InstagramMedia::class)` - all media from this account
- `hasMany(AudienceDemographic::class)` - demographics data

### Factory States
- Default: generates realistic Instagram account data
- `primary()`: sets `is_primary` to true
- `business()`: sets `account_type` to Business
- `creator()`: sets `account_type` to Creator
- `tokenExpired()`: sets `token_expires_at` to past date

### User Model Update
Add to `app/Models/User.php`:
```php
public function instagramAccounts(): HasMany
{
    return $this->hasMany(InstagramAccount::class);
}

public function primaryInstagramAccount(): HasOne
{
    return $this->hasOne(InstagramAccount::class)->where('is_primary', true);
}
```

## Files to Create/Modify
- `app/Models/InstagramAccount.php` (create via `php artisan make:model`)
- `database/factories/InstagramAccountFactory.php`
- `app/Models/User.php` (add relationships)

## Acceptance Criteria
- [ ] Model created with all fillable fields and casts
- [ ] Relationships defined with return type hints
- [ ] Factory produces valid model instances
- [ ] `access_token` is stored encrypted
- [ ] User model has `instagramAccounts()` and `primaryInstagramAccount()` relationships
- [ ] Tests verify factory and relationships
