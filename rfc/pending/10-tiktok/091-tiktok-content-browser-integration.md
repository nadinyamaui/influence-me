# 091 - TikTok Content Browser Integration

**Labels:** `feature`, `tiktok`, `ui`
**Depends on:** #077, #038, #039, #040

## Description

Extend the content browser and linking workflows so TikTok media appears alongside Instagram media with platform-aware filtering.

## Implementation

### Content Gallery
- Add platform filter controls driven by `PlatformType` values (`all` option included)
- Ensure at minimum `instagram`, `tiktok`, and `all` are available at this phase
- Render TikTok media cards with platform badge

### Content Detail + Linking
- Support TikTok media in detail modal
- Allow linking TikTok media to campaigns through the shared polymorphic `campaign_media` workflow

## Files to Modify
- `resources/views/pages/content-browser/index.blade.php`
- `resources/views/components/content-detail-modal.blade.php` (or equivalent)
- related Livewire/query layer

## Acceptance Criteria
- [ ] TikTok media appears in gallery for owner accounts
- [ ] Platform filters work correctly
- [ ] TikTok media can be linked to campaigns via polymorphic link flow
- [ ] Feature tests cover mixed-platform rendering and linking
