# 022 - Post-Signup Dashboard Demo Onboarding

**Labels:** `feature`, `ux`, `onboarding`
**Depends on:** #013, #020

## Description

After email/password signup/login, show a dashboard onboarding/empty state that helps new influencers explore backend functionality before connecting Instagram.

## Changes

- Keep post-signup destination as `/dashboard`.
- Add a dashboard empty-state card for users with no linked Instagram account.
- Card content:
  - Explain available backend demo capabilities.
  - Suggest optional Instagram connection later (non-blocking).

## Files to Modify (implementation phase)

- `resources/views/dashboard.blade.php`

## Acceptance Criteria

- [ ] Newly registered influencer lands on `/dashboard`
- [ ] User with no Instagram account sees demo onboarding state
- [ ] Existing users with linked account do not see incorrect empty-state messaging

## Test Requirements

- Feature test: no Instagram account => onboarding visible
- Feature test: linked Instagram account => onboarding hidden/replaced
