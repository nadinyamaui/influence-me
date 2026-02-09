# 038 - Content Browser Gallery Page

**Labels:** `feature`, `content`, `ui`
**Depends on:** #004, #013

## Description

Create a Livewire page at `/content` that displays all synced Instagram media in a visual grid/gallery format. This is where the influencer browses their content.

## Implementation

### Create Route
```php
Route::livewire('content', 'content.index')
    ->middleware(['auth'])
    ->name('content.index');
```

### Create Livewire Page
`resources/views/pages/content/index.blade.php`

### Page Content

**Header:** "Content" title

**Filters (horizontal bar):**
- Media type: All, Posts, Reels, Stories (Flux UI select or button group)
- Instagram account: dropdown of connected accounts (if multiple)
- Date range: from/to date pickers
- Sort by: Most Recent, Most Liked, Highest Reach, Best Engagement (Flux UI select)

**Gallery Grid:**
- Responsive grid: 4 columns on desktop, 2 on mobile
- Each card shows:
  - Thumbnail image (or video icon for reels)
  - Media type badge overlay (Post/Reel/Story)
  - Caption preview (first 50 chars, truncated)
  - Metrics overlay at bottom: likes, comments, reach
  - Published date
  - Click to open detail modal (#039)

**Pagination:** Use cursor pagination for performance with large media sets

**Empty State:** "No content synced yet. Connect an Instagram account and run a sync."

### Update Sidebar
Update sidebar `href="#"` for "Content" to `route('content.index')`.

### Query
```php
$media = InstagramMedia::query()
    ->whereHas('instagramAccount', fn ($q) => $q->where('user_id', auth()->id()))
    ->when($this->mediaType, fn ($q) => $q->where('media_type', $this->mediaType))
    ->when($this->accountId, fn ($q) => $q->where('instagram_account_id', $this->accountId))
    ->when($this->dateFrom, fn ($q) => $q->where('published_at', '>=', $this->dateFrom))
    ->when($this->dateTo, fn ($q) => $q->where('published_at', '<=', $this->dateTo))
    ->orderBy($this->sortField, $this->sortDirection)
    ->cursorPaginate(24);
```

## Files to Create
- `resources/views/pages/content/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update content link

## Acceptance Criteria
- [ ] Page renders at `/content` with grid of media
- [ ] Filters work: media type, account, date range
- [ ] Sort options work
- [ ] Thumbnails display correctly
- [ ] Metric overlays visible
- [ ] Cursor pagination works
- [ ] Empty state shown when no media
- [ ] Feature test verifies filtering and display
