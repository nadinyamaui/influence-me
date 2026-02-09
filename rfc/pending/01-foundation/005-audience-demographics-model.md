# 005 - AudienceDemographic Model and Factory

**Labels:** `feature`, `foundation`, `instagram`
**Depends on:** #001, #002, #003

## Description

Create the `AudienceDemographic` Eloquent model with relationships, casts, and factory.

## Model: `App\Models\AudienceDemographic`

### Mass Assignment
Use:
```php
protected $guarded = [];
```

Expected persisted attributes:
`instagram_account_id`, `type`, `dimension`, `value`, `recorded_at`

### Casts
```php
protected function casts(): array
{
    return [
        'type' => DemographicType::class,
        'value' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];
}
```

### Relationships
- `belongsTo(InstagramAccount::class)`

### Factory States
- Default: generates a random demographic entry
- `age()`: type = Age, dimension is an age range like "18-24"
- `gender()`: type = Gender, dimension is "Male" or "Female"
- `city()`: type = City, dimension is a city name
- `country()`: type = Country, dimension is a country name

## Files to Create
- `app/Models/AudienceDemographic.php`
- `database/factories/AudienceDemographicFactory.php`

## Acceptance Criteria
- [ ] Model created with `protected $guarded = [];` and required casts
- [ ] Relationship defined with return type hint
- [ ] Factory produces valid instances with all states
- [ ] Tests verify factory and relationship
