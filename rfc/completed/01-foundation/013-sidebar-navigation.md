# 013 - Sidebar Navigation Structure

**Labels:** `feature`, `ui`, `foundation`
**Depends on:** None (can be done in parallel with other foundation work)

## Description

Update the existing sidebar navigation to include all MVP sections. This is **boilerplate only** — no routes, no Livewire components, no page content. Just the sidebar menu items with placeholder `href="#"` links.

## Current State

The sidebar (`resources/views/layouts/app/sidebar.blade.php`) currently has only:
- Dashboard link
- External links (Repository, Documentation)

## Changes

Replace the sidebar nav content with the following groups and items. Use `href="#"` for all new items since routes don't exist yet. Keep the Dashboard link pointing to the existing route.

### Sidebar Groups

**Platform**
- Dashboard (icon: `home`, href: `route('dashboard')`) — already exists
- Content (icon: `image`) — browse synced Instagram content
- Schedule (icon: `calendar`) — timeline of planned posts
- Analytics (icon: `chart-bar`) — analytics dashboard

**Manage**
- Clients (icon: `users`) — client list
- Proposals (icon: `document-text`) — proposal list
- Invoices (icon: `banknotes`) — invoice list

**Instagram**
- Accounts (icon: `at-symbol`) — connected Instagram accounts

Remove the external "Repository" and "Documentation" links.

### Also update `resources/views/layouts/app/header.blade.php`
Update the header navbar to match the same structure (for the header layout variant). Remove external links.

## Files to Modify
- `resources/views/layouts/app/sidebar.blade.php`
- `resources/views/layouts/app/header.blade.php`

## Acceptance Criteria
- [ ] Sidebar shows all navigation groups and items
- [ ] All new items use `href="#"` as placeholder
- [ ] Dashboard link still works with existing route
- [ ] External links removed
- [ ] Icons are appropriate for each section
- [ ] Mobile sidebar toggle still works
- [ ] No broken routes or errors on page load
