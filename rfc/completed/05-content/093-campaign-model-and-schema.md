# 093 - Campaign Model and Schema (Content Domain)

**Labels:** `feature`, `content`, `campaigns`, `database`
**Depends on:** #006, #008, #011

## Description

Introduce campaigns as first-class entities owned by clients. Campaigns can optionally link to a proposal.

## Implementation

### Create `campaigns` table
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| client_id | foreignId | constrained, cascadeOnDelete |
| proposal_id | foreignId, nullable | constrained, nullOnDelete |
| name | string | required, unique per client |
| description | text, nullable | |
| timestamps | | |

Unique index:
- `[client_id, name]`

### Model relationships
- `Client hasMany Campaign`
- `Campaign belongsTo Client`
- `Campaign belongsTo Proposal` (nullable)

### Authorization
- Campaign operations must be scoped to the authenticated influencer's owned client records.
- Linking a proposal to campaign is allowed only when proposal and campaign belong to the same influencer and client.

## Files to Create/Modify
- `database/migrations/xxxx_create_campaigns_table.php`
- `app/Models/Campaign.php`
- `app/Models/Client.php` (add `campaigns()`)
- `app/Models/Proposal.php` (add reverse relation)
- `app/Policies/CampaignPolicy.php`

## Acceptance Criteria
- [ ] Campaign schema is created with required foreign keys and unique index
- [ ] Campaign belongs to a client
- [ ] Campaign may belong to a proposal (nullable)
- [ ] Ownership policy coverage exists for campaign actions
- [ ] Tests cover success and authorization boundaries
