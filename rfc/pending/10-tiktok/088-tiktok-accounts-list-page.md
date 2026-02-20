# 088 - TikTok Integration in Shared Accounts Page

**Labels:** `feature`, `tiktok`, `ui`
**Depends on:** #013, #028, #075

## Description

Integrate TikTok accounts into the existing shared connected-accounts experience for authenticated influencer users.

## Implementation

### Route + Page
- Reuse the existing connected accounts route/page
- Do not add a standalone `/tiktok-accounts` page

### Page Content
For each connected account show:
- Avatar and username
- Platform badge (`instagram`/`tiktok`)
- Display name
- Follower and video counts
- Sync status and last synced timestamp
- Token expiry warning state

### Empty State
Show a CTA to connect the first account or connect TikTok when only Instagram is linked.

## Files to Create

None required for a TikTok-only page.

## Files to Modify
- Existing shared accounts Livewire page/view
- Existing navigation where connected accounts are linked

## Acceptance Criteria
- [ ] Shared connected accounts page renders TikTok and Instagram accounts together
- [ ] Only owner accounts are listed
- [ ] Empty and partial-link states are visible
- [ ] Navigation continues to point to a single connected-accounts entry
- [ ] Feature tests cover mixed-platform and empty states
