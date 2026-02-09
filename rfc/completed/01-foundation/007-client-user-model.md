# 007 - ClientUser Model and Factory

**Labels:** `feature`, `foundation`, `clients`, `security`
**Depends on:** #001, #006

## Description

Create the `ClientUser` Eloquent model as a separate `Authenticatable` model for the client portal. This model represents a client's login credentials to access their portal.

## Model: `App\Models\ClientUser`

### Extends
`Illuminate\Foundation\Auth\User` (Authenticatable)

### Traits
`HasFactory`, `Notifiable`

### Fillable
`client_id`, `name`, `email`, `password`

### Hidden
`password`, `remember_token`

### Casts
```php
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}
```

### Relationships
- `belongsTo(Client::class)` - the client this account belongs to

### Factory
- Default: generates name, email, hashed password
- Belongs to a Client factory

## Files to Create
- `app/Models/ClientUser.php`
- `database/factories/ClientUserFactory.php`

## Acceptance Criteria
- [ ] Model extends `Authenticatable`
- [ ] `password` is cast as hashed
- [ ] Relationship defined with return type hint
- [ ] Factory produces valid instances
- [ ] Tests verify model can be used with auth guard
