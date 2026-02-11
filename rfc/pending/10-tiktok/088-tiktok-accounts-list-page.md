# 088 - TikTok Accounts List Page

**Labels:** `feature`, `tiktok`, `ui`
**Depends on:** #013, #076, #075

## Description

Create a Livewire page that lists all connected TikTok accounts for the authenticated influencer.

## Implementation

### Route + Page
- Add `/tiktok-accounts` route
- Create full-page Livewire view at `resources/views/pages/tiktok-accounts/index.blade.php`

### Page Content
For each account show:
- Avatar and username
- Display name
- Follower and video counts
- Sync status and last synced timestamp
- Token expiry warning state

### Empty State
Show a CTA to connect the first TikTok account.

## Files to Create
- `resources/views/pages/tiktok-accounts/index.blade.php`

## Files to Modify
- `routes/web.php`
- `resources/views/layouts/app/sidebar.blade.php`

## Acceptance Criteria
- [ ] Page loads for authenticated influencer users
- [ ] Only owner accounts are listed
- [ ] Empty state is visible when no accounts exist
- [ ] Sidebar navigation links correctly
- [ ] Feature test covers populated and empty states
