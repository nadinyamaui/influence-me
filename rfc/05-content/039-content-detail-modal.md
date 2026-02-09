# 039 - Content Detail Modal

**Labels:** `feature`, `content`, `ui`
**Depends on:** #038

## Description

Add a detail modal/slide-over that opens when clicking a media item in the content gallery. Shows full metrics, caption, and actions.

## Implementation

### Modal Content (Flux UI modal or slide-over)

**Media Preview:**
- Full-size image or video thumbnail
- Link to original Instagram post (permalink, opens in new tab)

**Caption:**
- Full caption text (scrollable if long)

**Metrics Grid (2 columns):**
| Metric | Value |
|--------|-------|
| Likes | {count} |
| Comments | {count} |
| Saved | {count} |
| Shares | {count} |
| Reach | {count} |
| Impressions | {count} |
| Engagement Rate | {rate}% |

**Meta Info:**
- Published: {date}
- Media Type: {type badge}
- Account: @{username}

**Linked Clients Section:**
- List of clients this media is linked to (with campaign name if set)
- "Link to Client" button (implemented in #040)
- "Unlink" action per client

**Actions:**
- "View on Instagram" link
- "Link to Client" button
- Close modal

### Livewire Integration
Use `wire:click` on gallery cards to set the selected media ID, then show the modal:
```blade
<flux:modal wire:model="showDetailModal">
    <!-- modal content -->
</flux:modal>
```

## Files to Modify
- `resources/views/pages/content/index.blade.php` — add modal component

## Acceptance Criteria
- [ ] Clicking a media card opens the detail modal
- [ ] All metrics displayed correctly
- [ ] Full caption shown
- [ ] Link to original Instagram post works
- [ ] Linked clients listed
- [ ] Close modal returns to gallery
- [ ] Feature test verifies modal data display
