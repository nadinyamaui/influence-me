# 041 - Client Detail Linked Content Tab

**Labels:** `feature`, `content`, `clients`, `ui`
**Depends on:** #034, #040

## Description

Populate the "Content" tab on the client detail page with all Instagram media linked to this client. Group by campaign name.

## Implementation

### Update Client Detail Page
Replace the placeholder in the Content tab with actual content.

### Tab Content

**Campaign Groups:**
- Group linked media by `campaign_name` (from pivot)
- Each group has a header showing: campaign name (or "Uncategorized" if null), count of posts, aggregate reach
- Media displayed in a compact grid within each group

**Per-Media Card (compact):**
- Small thumbnail
- Caption preview (30 chars)
- Likes + reach numbers
- Published date
- "Unlink" action

**Aggregate Stats Banner (top of tab):**
- Total linked posts
- Total reach across all linked content
- Total impressions
- Average engagement rate

**Empty State:** "No content linked to this client yet. Go to the Content browser to link posts."

### Query
```php
$linkedMedia = $client->instagramMedia()
    ->orderByPivot('campaign_name')
    ->orderBy('published_at', 'desc')
    ->get()
    ->groupBy('pivot.campaign_name');
```

## Files to Modify
- `resources/views/pages/clients/show.blade.php` — populate Content tab

## Acceptance Criteria
- [ ] Content tab shows all linked media grouped by campaign
- [ ] Aggregate stats banner shows correct totals
- [ ] Unlink action works from this view
- [ ] Empty state shown when no linked content
- [ ] Feature test verifies display and grouping

## Forward Compatibility Note

This RFC remains historical for the initial implementation. Campaign-entity and polymorphic campaign-content linking requirements are defined in RFCs `094`, `096`, and `097`.
