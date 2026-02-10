# 020 - Influencer Email/Password Access (Demo-Ready)

**Labels:** `feature`, `authentication`
**Depends on:** #015

## Description

Allow influencer users to register and log in via existing Fortify email/password flows so they can access the backend before Instagram OAuth is required.

## Changes

- Keep Fortify registration/login/password-reset features enabled.
- Keep `CreateNewUser` action active.
- Keep login/register/reset views active.
- Keep redirect after auth to `/dashboard`.
- Explicitly mark RFC `017` ("Remove Email/Password Authentication") as deferred/superseded by this RFC.

## Files to Modify (implementation phase)

- `config/fortify.php` (verification only unless currently disabled)
- `app/Providers/FortifyServiceProvider.php` (verification only unless currently disabled)
- `app/Actions/Fortify/CreateNewUser.php` (verification only)

## Acceptance Criteria

- [ ] `GET /register` returns 200
- [ ] `POST /register` creates influencer user and authenticates session
- [ ] `GET /login` returns 200
- [ ] `POST /login` authenticates valid credentials
- [ ] Authenticated influencer is redirected to `/dashboard`
- [ ] Existing auth tests pass

## Test Requirements

- Feature tests for register/login success and invalid credentials
- Guard boundary confirmation remains intact for `client` guard routes
