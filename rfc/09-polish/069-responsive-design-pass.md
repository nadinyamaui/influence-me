# 069 - Responsive Design Pass

**Labels:** `enhancement`, `ui`, `accessibility`
**Depends on:** All UI issues

## Description

Verify and fix responsive design across all pages. Ensure the app works well from 375px (mobile) to 1920px (desktop).

## Pages to Review
- Dashboard/Analytics
- Instagram Accounts
- Content Browser (gallery grid)
- Schedule Timeline
- Client List, Create, Edit, Detail
- Proposal List, Create, Edit, Preview
- Invoice List, Create, Edit, Preview
- Client Portal (all pages)
- Settings pages
- Login page

## Focus Areas

### Tables
- Convert to card layout on mobile (stack columns)
- Horizontal scroll as fallback for complex tables

### Charts
- Charts must be responsive (Chart.js handles this by default)
- May need reduced labels on mobile

### Forms
- Full-width inputs on mobile
- Stacked layout for multi-column forms

### Gallery Grid
- 4 columns desktop → 2 columns tablet → 1 column mobile

### Modals
- Full-screen on mobile (Flux UI may handle this)

### Navigation
- Sidebar collapses to hamburger on mobile (already implemented)
- Portal sidebar same behavior

## Acceptance Criteria
- [ ] All pages render correctly at 375px width
- [ ] All pages render correctly at 768px width
- [ ] All pages render correctly at 1920px width
- [ ] No horizontal scroll on any page (except intended table scroll)
- [ ] Touch targets are at least 44x44px on mobile
- [ ] Text is readable without zooming on mobile
