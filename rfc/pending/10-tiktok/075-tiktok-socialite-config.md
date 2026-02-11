# 075 - TikTok Socialite Service Configuration

**Labels:** `feature`, `tiktok`, `backend`, `auth`
**Depends on:** #074

## Description

Add TikTok OAuth provider configuration so authenticated influencers can link TikTok accounts from the app.

## Implementation

### Configure Provider
- Register TikTok provider settings in services config
- Add env variables for client ID, client secret, and redirect URI

### Add Driver Wiring
- Ensure Socialite can resolve TikTok provider
- Add tests for configuration resolution

## Files to Modify
- `config/services.php`
- `.env.example`
- `composer.json` (if provider package is required)

## Acceptance Criteria
- [ ] TikTok provider config is present and environment-driven
- [ ] Configuration test verifies expected keys and values
- [ ] No web or client guard flow is broken
