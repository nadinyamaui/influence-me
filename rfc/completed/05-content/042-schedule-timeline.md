# 042 - Schedule Timeline Page

**Labels:** `feature`, `content`, `ui`
**Depends on:** #010, #013, #093

## Description

Create a Livewire page at `/schedule` showing a simple chronological list/timeline of planned posts. Not a full calendar — just a sorted list with CRUD.

## Implementation

### Create Route
```php
Route::livewire('schedule', 'schedule.index')
    ->middleware(['auth'])
    ->name('schedule.index');
```

### Create Livewire Page
`resources/views/pages/schedule/index.blade.php`

### Page Content

**Header:** "Schedule" title with "Add Post" button

**Filters:**
- Status: All, Planned, Published, Cancelled
- Client: dropdown of clients (or All)
- Campaign: dropdown of user's campaigns (or All)
- Platform: enum-backed options from `PlatformType` (plus All)
- Platform account: scoped dropdown based on selected platform
- Media Type: All, Post, Reel, Story
- Date range: from/to
- Apply all filters in the Livewire component query (not by filtering data in Blade)

**Timeline List:**
Each entry shows:
- Date/time (formatted nicely, grouped by day)
- Status badge: Planned (blue), Published (green), Cancelled (gray)
- Title
- Description (truncated)
- Client name (or "No client")
- Campaign name (or "No campaign")
- Platform badge (`Instagram`, `TikTok`, `Snapchat`, `YouTube`, `Twitch`, `Kick`)
- Media type badge (`Post`, `Reel`, `Story`) when present
- Platform account handle/channel name
- Proposal context badge when campaign is linked to a proposal (`Draft`, `Sent`, `Approved`, `Rejected`, `Revised`)
- Actions: Edit, Delete, Mark as Published, Mark as Cancelled

**Day Grouping:**
Group posts by day with date headers:
```
--- February 15, 2026 ---
10:00 AM  Product Review for @brand  [Planned]
3:00 PM   Story Series for @client2  [Planned]

--- February 16, 2026 ---
...
```

### Create/Edit Modal
Flux UI modal with:
- Title (required, text input)
- Description (optional, textarea)
- Client (optional, select from user's clients)
- Campaign (optional, select scoped to selected client when present)
- Platform (required, enum-backed select)
- Platform account (required, select scoped to platform and authenticated influencer)
- Media Type (nullable, select: Post, Reel, Story)
- Date & Time (required, datetime picker)
- Status (select: Planned, Published, Cancelled)
- Save / Cancel buttons

### Form Request
`App\Http\Requests\StoreScheduledPostRequest`:
- `title`: required, string, max:255
- `description`: nullable, string, max:5000
- `client_id`: nullable, exists:clients,id
- `campaign_id`: nullable, exists:campaigns,id (scoped to user/client)
- `platform`: required, in:instagram,tiktok,snapchat,youtube,twitch,kick
- `platform_account_type`: required, string
- `platform_account_id`: required, integer
- `media_type`: nullable, in:post,reel,story
- `scheduled_at`: required, date, after:now (for new posts)
- `status`: required, in:planned,published,cancelled

### Update Sidebar
Update sidebar `href="#"` for "Schedule" to `route('schedule.index')`.

## Files to Create
- `resources/views/pages/schedule/index.blade.php`
- `app/Http/Requests/StoreScheduledPostRequest.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update schedule link

## Acceptance Criteria
- [ ] Page renders at `/schedule` with chronological list
- [ ] Posts grouped by day
- [ ] Filters work: status, client, campaign, platform, account, date range
- [ ] Campaign and media type filters work
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Create modal works with all fields
- [ ] Edit modal pre-fills existing data
- [ ] Timeline entries show campaign, platform, account, and media-type context
- [ ] Timeline entries show proposal status context when available
- [ ] Status can be changed (Planned -> Published/Cancelled)
- [ ] Delete with confirmation works
- [ ] Authorization enforced
- [ ] Feature tests cover CRUD and filtering
