# 008 - Proposal Model and Factory

**Labels:** `feature`, `foundation`, `proposals`
**Depends on:** #001, #002, #006

## Description

Create the `Proposal` Eloquent model with relationships, casts, and factory. Update User model with relationship.

## Model: `App\Models\Proposal`

### Mass Assignment
Use:
```php
protected $guarded = [];
```

Expected persisted attributes:
`user_id`, `client_id`, `title`, `content`, `status`, `revision_notes`, `sent_at`, `responded_at`

### Casts
```php
protected function casts(): array
{
    return [
        'status' => ProposalStatus::class,
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
    ];
}
```

### Relationships
- `belongsTo(User::class)` - the influencer who created it
- `belongsTo(Client::class)` - the client it's for

### Factory States
- Default: draft proposal with markdown content
- `draft()`: status = Draft
- `sent()`: status = Sent, sent_at filled
- `approved()`: status = Approved, responded_at filled
- `rejected()`: status = Rejected, responded_at filled
- `revised()`: status = Revised, revision_notes filled

### User Model Update
Add to `app/Models/User.php`:
```php
public function proposals(): HasMany
{
    return $this->hasMany(Proposal::class);
}
```

## Files to Create/Modify
- `app/Models/Proposal.php`
- `database/factories/ProposalFactory.php`
- `app/Models/User.php` (add relationship)

## Acceptance Criteria
- [ ] Model created with `protected $guarded = [];` and required casts
- [ ] Relationships defined with return type hints
- [ ] Factory produces valid instances with all states
- [ ] User model has `proposals()` relationship
- [ ] Tests verify factory and relationships
