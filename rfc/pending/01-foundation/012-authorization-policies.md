# 012 - Authorization Policies for All Models

**Labels:** `feature`, `foundation`, `security`
**Depends on:** #003, #004, #006, #008, #009, #010

## Description

Create Laravel policies for each model to ensure users can only access their own data. The influencer (User) should only access their own resources. ClientUser should only access data belonging to their associated Client.

## Policies to Create

### `InstagramAccountPolicy`
- `view`: user owns the account
- `update`: user owns the account
- `delete`: user owns the account, not the last account

### `ClientPolicy`
- `viewAny`: authenticated user (sees only their own via scoping)
- `view`: user owns the client
- `create`: any authenticated user
- `update`: user owns the client
- `delete`: user owns the client

### `ProposalPolicy`
- `viewAny`: authenticated user
- `view`: user owns the proposal OR ClientUser's client matches
- `create`: any authenticated user
- `update`: user owns the proposal
- `delete`: user owns the proposal
- `send`: user owns the proposal AND status is Draft

### `InvoicePolicy`
- `viewAny`: authenticated user
- `view`: user owns the invoice OR ClientUser's client matches
- `create`: any authenticated user
- `update`: user owns the invoice AND status is Draft
- `delete`: user owns the invoice AND status is Draft

### `ScheduledPostPolicy`
- `viewAny`: authenticated user
- `view`: user owns the scheduled post
- `create`: any authenticated user
- `update`: user owns the scheduled post
- `delete`: user owns the scheduled post

### `InstagramMediaPolicy`
- `view`: user owns the media (via instagram account)
- `linkToClient`: user owns the media

## Files to Create
- `app/Policies/InstagramAccountPolicy.php`
- `app/Policies/ClientPolicy.php`
- `app/Policies/ProposalPolicy.php`
- `app/Policies/InvoicePolicy.php`
- `app/Policies/ScheduledPostPolicy.php`
- `app/Policies/InstagramMediaPolicy.php`

## Acceptance Criteria
- [ ] All policies created via `php artisan make:policy`
- [ ] Each policy has appropriate methods with return type hints
- [ ] Policies are auto-discovered by Laravel
- [ ] Feature tests verify each policy method (authorized and unauthorized)
- [ ] Unauthorized access returns 403
