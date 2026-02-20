# 074 - TikTok Developer App Setup Documentation

**Labels:** `feature`, `tiktok`, `docs`
**Depends on:** #073

## Description

Document TikTok developer setup for sandbox and production apps so engineering can configure OAuth, API scopes, and callback URLs consistently across environments.

## Implementation

### Create Setup Guide
Include:
- TikTok developer console project creation
- Sandbox vs production app separation
- Required redirect URIs per environment
- Required scopes for profile/media/insights read
- App review and go-live checklist

### Environment Configuration
Define required env vars and where they are consumed.

### Security Notes
Document secret handling, token storage expectations, and rotation guidance.

## Files to Create
- `docs/integrations/tiktok-app-setup.md`

## Acceptance Criteria
- [ ] Setup guide exists with sandbox and production steps
- [ ] Required scopes are explicitly listed
- [ ] Redirect URI and env var requirements are documented
- [ ] Security and rollout checklist included
