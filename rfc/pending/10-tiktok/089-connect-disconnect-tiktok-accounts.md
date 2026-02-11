# 089 - Connect and Disconnect TikTok Accounts

**Labels:** `feature`, `tiktok`, `backend`, `ui`, `auth`
**Depends on:** #075, #076, #088

## Description

Implement TikTok account linking/unlinking flows for influencer users using OAuth and secure token storage.

## Implementation

### Connect Flow
- Add connect action/button and redirect to TikTok OAuth
- Handle callback, exchange token, and persist `TikTokAccount`

### Disconnect Flow
- Confirm unlink action
- Revoke token when API supports it, then delete or soft-delete local account record

### Security
- Enforce ownership checks and CSRF/session protections on actions

## Files to Modify
- `routes/web.php`
- `app/Http/Controllers/*` or Livewire page action endpoints
- `resources/views/pages/tiktok-accounts/index.blade.php`

## Acceptance Criteria
- [ ] Influencer can connect a TikTok account via OAuth
- [ ] Duplicate linking is prevented
- [ ] Influencer can disconnect own account safely
- [ ] Unauthorized users cannot link/unlink others' accounts
- [ ] Feature tests cover success, duplicate, and authorization paths
