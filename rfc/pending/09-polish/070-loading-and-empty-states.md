# 070 - Loading States and Empty States

**Labels:** `enhancement`, `ui`
**Depends on:** All UI issues

## Description

Add consistent loading states and helpful empty states across all pages.

## Loading States

Add `wire:loading` indicators for all async operations:

### Navigation
```blade
<div wire:loading wire:target="search">
    <flux:icon name="loader" class="animate-spin" />
</div>
```

### Form Submissions
Disable submit buttons during processing:
```blade
<flux:button wire:loading.attr="disabled" wire:target="save">
    <span wire:loading.remove wire:target="save">Save</span>
    <span wire:loading wire:target="save">Saving...</span>
</flux:button>
```

### Page Transitions
Loading bar or spinner during `wire:navigate` transitions.

### Sync Operations
Already handled in #030, verify consistency.

## Empty States

Add meaningful empty states with call-to-action for every list page:

| Page | Empty State Message | CTA |
|------|-------------------|-----|
| Clients | "No clients yet" | "Add your first client" |
| Proposals | "No proposals yet" | "Create your first proposal" |
| Invoices | "No invoices yet" | "Create your first invoice" |
| Content | "No content synced yet" | "Connect an Instagram account" |
| Schedule | "Nothing scheduled" | "Plan your first post" |
| Instagram Accounts | "No accounts connected" | "Connect Instagram" |
| Analytics | "Not enough data" | "Sync your Instagram data" |

### Empty State Component
Create a reusable Blade component:
```blade
<x-empty-state
    icon="document"
    title="No proposals yet"
    description="Create your first proposal to send to a client."
    :action-url="route('proposals.create')"
    action-label="Create Proposal"
/>
```

## Files to Create
- `resources/views/components/empty-state.blade.php`

## Files to Modify
- All list page views (add loading and empty states)

## Acceptance Criteria
- [ ] Every form has loading state on submit button
- [ ] Every list page has an empty state with CTA
- [ ] Empty state component is reusable
- [ ] Loading indicators are consistent in style
- [ ] No flash of empty state when data is loading
