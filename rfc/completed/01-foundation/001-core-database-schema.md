# 001 - Core Database Schema Migrations

**Labels:** `feature`, `database`, `foundation`
**Depends on:** None

## Description

Create all database migrations for the MVP data model. This establishes the schema that all features depend on. The User table must also be modified to support Instagram OAuth (nullable password).

## Migrations to Create

### Modify existing `users` table
- Make `password` nullable (Instagram OAuth users won't have a password)
- Add `instagram_primary_account_id` (nullable, unsigned big integer) - will be foreign keyed after instagram_accounts exists

### `instagram_accounts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| user_id | foreignId | constrained, cascadeOnDelete |
| instagram_user_id | string | unique, the IG user ID |
| username | string | |
| name | string, nullable | |
| biography | text, nullable | |
| profile_picture_url | string, nullable | |
| account_type | string | Business or Creator |
| followers_count | unsignedInteger, default 0 | |
| following_count | unsignedInteger, default 0 | |
| media_count | unsignedInteger, default 0 | |
| access_token | text | encrypted at model level |
| token_expires_at | timestamp, nullable | |
| is_primary | boolean, default false | |
| last_synced_at | timestamp, nullable | |
| sync_status | string, default 'idle' | idle, syncing, failed |
| last_sync_error | text, nullable | |
| timestamps | | |

### `instagram_media`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| instagram_account_id | foreignId | constrained, cascadeOnDelete |
| instagram_media_id | string | unique, the IG media ID |
| media_type | string | Post, Reel, Story |
| caption | text, nullable | |
| permalink | string, nullable | |
| media_url | string, nullable | |
| thumbnail_url | string, nullable | |
| published_at | timestamp, nullable | |
| like_count | unsignedInteger, default 0 | |
| comments_count | unsignedInteger, default 0 | |
| saved_count | unsignedInteger, default 0 | |
| shares_count | unsignedInteger, default 0 | |
| reach | unsignedInteger, default 0 | |
| impressions | unsignedInteger, default 0 | |
| engagement_rate | decimal(5,2), default 0 | |
| timestamps | | |

### `audience_demographics`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| instagram_account_id | foreignId | constrained, cascadeOnDelete |
| type | string | Age, Gender, City, Country |
| dimension | string | e.g. "18-24", "Male", "London" |
| value | decimal(5,2) | percentage |
| recorded_at | timestamp | |
| timestamps | | |

Index: `[instagram_account_id, type, recorded_at]`

### `clients`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| user_id | foreignId | constrained, cascadeOnDelete |
| name | string | |
| email | string, nullable | |
| company_name | string, nullable | |
| type | string | Brand, Individual |
| phone | string, nullable | |
| notes | text, nullable | |
| timestamps | | |

### `client_users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| client_id | foreignId | constrained, cascadeOnDelete |
| name | string | |
| email | string, unique | |
| password | string | |
| remember_token | rememberToken | |
| timestamps | | |

### `proposals`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| user_id | foreignId | constrained, cascadeOnDelete |
| client_id | foreignId | constrained, cascadeOnDelete |
| title | string | |
| content | longText | markdown |
| status | string, default 'draft' | Draft, Sent, Approved, Rejected, Revised |
| revision_notes | text, nullable | from client |
| sent_at | timestamp, nullable | |
| responded_at | timestamp, nullable | |
| timestamps | | |

### `invoices`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| user_id | foreignId | constrained, cascadeOnDelete |
| client_id | foreignId | constrained, cascadeOnDelete |
| invoice_number | string, unique | |
| status | string, default 'draft' | Draft, Sent, Paid, Overdue, Cancelled |
| due_date | date | |
| subtotal | decimal(10,2), default 0 | |
| tax_rate | decimal(5,2), default 0 | |
| tax_amount | decimal(10,2), default 0 | |
| total | decimal(10,2), default 0 | |
| stripe_payment_link | string, nullable | |
| stripe_session_id | string, nullable | |
| paid_at | timestamp, nullable | |
| notes | text, nullable | |
| timestamps | | |

### `invoice_items`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| invoice_id | foreignId | constrained, cascadeOnDelete |
| description | string | |
| quantity | decimal(8,2) | |
| unit_price | decimal(10,2) | |
| total | decimal(10,2) | |
| timestamps | | |

### `campaign_media` (polymorphic campaign-content link)
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| campaign_id | foreignId | constrained, cascadeOnDelete |
| platform | string | enum-backed `PlatformType` |
| linkable_type | string | target model class |
| linkable_id | unsignedBigInteger | target model key |
| notes | text, nullable | |
| timestamps | | |

Unique:
- `[campaign_id, linkable_type, linkable_id]`

Indexes:
- `[platform, linkable_type, linkable_id]`

### `scheduled_posts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| user_id | foreignId | constrained, cascadeOnDelete |
| client_id | foreignId, nullable | constrained, nullOnDelete |
| campaign_id | foreignId, nullable | constrained, nullOnDelete |
| platform | string | enum-backed `PlatformType` |
| platform_account_type | string | morph type for connected account model |
| platform_account_id | unsignedBigInteger | morph id for connected account model |
| title | string | |
| description | text, nullable | |
| media_type | string, nullable | Post, Reel, Story when applicable |
| scheduled_at | timestamp | |
| status | string, default 'planned' | Planned, Published, Cancelled |
| timestamps | | |

Index:
- `[platform, platform_account_type, platform_account_id]`

## Files to Create/Modify
- `database/migrations/xxxx_modify_users_table_for_oauth.php`
- `database/migrations/xxxx_create_instagram_accounts_table.php`
- `database/migrations/xxxx_create_instagram_media_table.php`
- `database/migrations/xxxx_create_audience_demographics_table.php`
- `database/migrations/xxxx_create_clients_table.php`
- `database/migrations/xxxx_create_client_users_table.php`
- `database/migrations/xxxx_create_proposals_table.php`
- `database/migrations/xxxx_create_invoices_table.php`
- `database/migrations/xxxx_create_invoice_items_table.php`
- `database/migrations/xxxx_create_campaign_media_table.php`
- `database/migrations/xxxx_create_scheduled_posts_table.php`

## Acceptance Criteria
- [ ] All migrations run with `php artisan migrate:fresh`
- [ ] All foreign keys and indexes are properly defined
- [ ] Rollbacks work cleanly
- [ ] `users.password` is nullable
- [ ] `campaign_media` polymorphic unique constraint prevents duplicate campaign-content links
