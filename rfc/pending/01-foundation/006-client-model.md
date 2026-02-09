# 006 - Client Model and Factory

**Labels:** `feature`, `foundation`, `clients`
**Depends on:** #001, #002

## Description

Create the `Client` Eloquent model with relationships, casts, and factory. Also update the `User` model to add the `hasMany` relationship.

## Model: `App\Models\Client`

### Fillable
`user_id`, `name`, `email`, `company_name`, `type`, `phone`, `notes`

### Casts
```php
protected function casts(): array
{
    return [
        'type' => ClientType::class,
    ];
}
```

### Relationships
- `belongsTo(User::class)` - the owning influencer
- `hasOne(ClientUser::class)` - portal account
- `hasMany(Proposal::class)`
- `hasMany(Invoice::class)`
- `belongsToMany(InstagramMedia::class, 'campaign_media')->withPivot('campaign_name', 'notes')->withTimestamps()` - linked content

### Factory States
- Default: generates realistic client data
- `brand()`: type = Brand, includes company_name
- `individual()`: type = Individual, no company_name

### User Model Update
Add to `app/Models/User.php`:
```php
public function clients(): HasMany
{
    return $this->hasMany(Client::class);
}
```

## Files to Create/Modify
- `app/Models/Client.php`
- `database/factories/ClientFactory.php`
- `app/Models/User.php` (add relationship)

## Acceptance Criteria
- [ ] Model created with all fillable fields and casts
- [ ] Relationships defined with return type hints
- [ ] Factory produces valid instances with states
- [ ] User model has `clients()` relationship
- [ ] Tests verify factory and relationships
