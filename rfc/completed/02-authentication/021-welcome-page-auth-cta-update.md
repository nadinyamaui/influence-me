# 021 - Welcome Page Auth CTA and Messaging Update

**Labels:** `feature`, `ux`, `authentication`
**Depends on:** #000, #020

## Description

Update welcome-page auth CTAs and copy so influencers can start with email/password while preserving client portal access and future Instagram connect messaging.

## Changes

- Replace influencer CTA links from Instagram redirect URLs to:
  - Primary: `register`
  - Secondary: `login`
- Keep client portal CTA (`/client/login`) unchanged.
- Remove Instagram-only gating language such as:
  - "No password required"
  - "Instagram OAuth required for all influencer accounts"
- Replace with neutral copy: users can connect Instagram later.

## Files to Modify (implementation phase)

- `resources/views/welcome.blade.php`

## Acceptance Criteria

- [ ] Welcome page has working influencer `register`/`login` CTAs
- [ ] No broken links to unimplemented Instagram auth routes
- [ ] Client portal CTA remains visible and unchanged
- [ ] Instagram-only requirement copy is removed

## Test Requirements

- Feature test asserting welcome CTAs and text changes
- Regression test ensuring client portal link still renders
