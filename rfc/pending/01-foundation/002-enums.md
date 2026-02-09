# 002 - PHP Enums for Status and Type Fields

**Labels:** `feature`, `foundation`
**Depends on:** #001

## Description

Create all backed string enums used across the application. These enums will be used in model `casts()` methods and throughout the codebase for type safety.

## Enums to Create

### `App\Enums\MediaType`
```
Post, Reel, Story
```

### `App\Enums\ClientType`
```
Brand, Individual
```

### `App\Enums\ProposalStatus`
```
Draft, Sent, Approved, Rejected, Revised
```

### `App\Enums\InvoiceStatus`
```
Draft, Sent, Paid, Overdue, Cancelled
```

### `App\Enums\ScheduledPostStatus`
```
Planned, Published, Cancelled
```

### `App\Enums\DemographicType`
```
Age, Gender, City, Country
```

### `App\Enums\AccountType`
```
Business, Creator
```

### `App\Enums\SyncStatus`
```
Idle, Syncing, Failed
```

## Conventions
- All enums must be **backed string enums** (`enum X: string`)
- Keys use **TitleCase** (e.g., `case Draft = 'draft';`)
- Values are **lowercase** strings
- Place all enums in `app/Enums/` directory

## Files to Create
- `app/Enums/MediaType.php`
- `app/Enums/ClientType.php`
- `app/Enums/ProposalStatus.php`
- `app/Enums/InvoiceStatus.php`
- `app/Enums/ScheduledPostStatus.php`
- `app/Enums/DemographicType.php`
- `app/Enums/AccountType.php`
- `app/Enums/SyncStatus.php`

## Acceptance Criteria
- [ ] All enums are backed string enums
- [ ] Keys use TitleCase convention
- [ ] Values are lowercase strings
- [ ] Unit tests verify enum values and cases
