# 089 - Connect and Disconnect TikTok Accounts

**Labels:** `feature`, `tiktok`, `backend`, `ui`, `auth`
**Depends on:** #075, #088

## Description

Implement TikTok linking/unlinking flows in the shared connected-accounts workflow for influencer users using OAuth and secure token storage.

## Implementation

### Connect Flow
- Add connect action/button and redirect to TikTok OAuth
- Handle callback, exchange token, and persist/update `SocialAccount` with `social_network = tiktok`

### Disconnect Flow
- Confirm unlink action
- Revoke token when API supports it, then delete or soft-delete the TikTok `SocialAccount` record

### Security
- Enforce ownership checks and CSRF/session protections on actions

## Files to Modify
- `routes/web.php`
- `app/Http/Controllers/*` or Livewire page action endpoints
- existing shared accounts page/view

## Acceptance Criteria
- [ ] Influencer can connect a TikTok account via OAuth
- [ ] Duplicate linking is prevented
- [ ] Influencer can disconnect own account safely
- [ ] Unauthorized users cannot link/unlink others' accounts
- [ ] Feature tests cover success, duplicate, and authorization paths
