# 040 - Link Content to Client

**Labels:** `feature`, `content`, `campaigns`
**Depends on:** #006, #011, #038

## Description

Implement the ability to link Instagram media to clients, forming the basis of campaign tracking. Support both single and batch linking.

## Implementation

### Single Link (from detail modal)
Add a "Link to Client" button in the content detail modal (#039):
1. Opens a sub-modal/dropdown with:
   - Client selector (search/dropdown of user's clients)
   - Campaign name input (optional, text field)
   - Notes input (optional, textarea)
   - Save button
2. On save: attach the media to the client via the `campaign_media` pivot
3. Show success toast
4. Update linked clients list in the detail modal

### Batch Link (from gallery)
Add batch selection mode to the content gallery:
1. "Select" toggle button in the header
2. When active, clicking cards selects/deselects them (checkbox overlay)
3. Selection bar appears at bottom: "{N} selected" + "Link to Client" button + "Cancel"
4. "Link to Client" opens the same modal as single link
5. On save: attach all selected media to the chosen client with same campaign name
6. Clear selection after linking

### Unlink
In the detail modal's linked clients section:
- "Unlink" button per client
- Confirmation: "Remove link to {client name}?"
- On confirm: detach from pivot table
- Update UI immediately

### Livewire Logic
```php
public function linkToClient(int $mediaId, int $clientId, ?string $campaignName, ?string $notes): void
{
    $media = InstagramMedia::findOrFail($mediaId);
    $this->authorize('linkToClient', $media);

    $media->clients()->syncWithoutDetaching([
        $clientId => [
            'campaign_name' => $campaignName,
            'notes' => $notes,
        ]
    ]);
}

public function batchLinkToClient(array $mediaIds, int $clientId, ?string $campaignName): void
{
    // Same logic for each media ID
}

public function unlinkFromClient(int $mediaId, int $clientId): void
{
    $media = InstagramMedia::findOrFail($mediaId);
    $media->clients()->detach($clientId);
}
```

## Files to Modify
- `resources/views/pages/content/index.blade.php` — add batch selection and link modal

## Acceptance Criteria
- [ ] Single media can be linked to a client with optional campaign name
- [ ] Batch selection mode works
- [ ] Batch linking links all selected media
- [ ] Unlink removes the association
- [ ] Duplicate links prevented (unique constraint)
- [ ] Authorization enforced
- [ ] Feature tests cover link, batch link, and unlink

## Forward Compatibility Note

This RFC remains historical for the initial implementation. Campaign-first linking requirements are defined in RFCs `093`, `094`, and `096`.
