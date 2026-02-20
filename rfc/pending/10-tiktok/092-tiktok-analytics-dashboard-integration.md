# 092 - TikTok Analytics Dashboard Integration

**Labels:** `feature`, `tiktok`, `analytics`, `ui`
**Depends on:** #084, #085, #058, #059, #060, #061, #062, #063, #065

## Description

Integrate TikTok metrics into analytics dashboards with platform-scoped toggles for influencer and client-safe views.

## Implementation

### Dashboard Updates
- Add platform-level filters and cards that include TikTok metrics
- Keep platform toggles enum-driven and extendable through `PlatformType`
- Ensure at minimum `instagram`, `tiktok`, and `all` are available in this phase
- Update trend and breakdown charts to support TikTok datasets

### Ownership + Guard Rules
- Respect influencer ownership and client scoping constraints for TikTok-backed analytics

## Files to Modify
- analytics Livewire pages/components and chart query services
- client analytics views where scoped TikTok data is allowed

## Acceptance Criteria
- [ ] TikTok metrics are included in analytics calculations
- [ ] Platform filter toggles between Instagram/TikTok/All
- [ ] Client portal only shows authorized client-scoped data
- [ ] Feature tests cover platform filters and authorization boundaries
