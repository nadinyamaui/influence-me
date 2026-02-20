# 101 - Pricing Products CRUD

**Labels:** `feature`, `ui`, `pricing`
**Depends on:** #100, #013, #012

## Description

Create influencer-facing CRUD pages to manage reusable pricing products (post/reel/story deliverables and package-type items), with platform-aware pricing.

## Implementation

### Routes
Add authenticated influencer routes:
- `GET /pricing/products` -> products index
- `GET /pricing/products/create` -> create page
- `GET /pricing/products/{product}/edit` -> edit page

### UI
Create Livewire pages for:
- Product list with search, status filter (`active` / `archived`), platform filter.
- Product create form.
- Product edit form.

Product form fields:
- Name
- Platform (`instagram`, `tiktok`, `snapchat`, `youtube`, `twitch`, `kick`)
- Media type (`post`, `reel`, `story`) or nullable when generic/non-mapped
- Billing unit (`deliverable`, `package`)
- Base price
- Currency (default `USD`)
- Active toggle

Use Flux form fields and rely on Flux validation presentation.

### Query Layer
Implement query composition in `CatalogProductBuilder`:
- `forUser(int $userId)`
- `search(?string $term)`
- `filterByPlatform(?string $platform)`
- `filterByActive(?bool $active)`
- `applySort(string $sort)`

Platform filter options must come from `PlatformType` enum values (no hardcoded two-platform list).

### Service Layer
Create `CatalogProductService` for create/update/archive workflows.

### Authorization
- Enforce ownership via policy and builder scoping.

## Files to Create
- `app/Livewire/Pricing/Products/Index.php`
- `app/Livewire/Pricing/Products/Create.php`
- `app/Livewire/Pricing/Products/Edit.php`
- `resources/views/pages/pricing/products/index.blade.php`
- `resources/views/pages/pricing/products/create.blade.php`
- `resources/views/pages/pricing/products/edit.blade.php`
- `app/Http/Requests/StoreCatalogProductRequest.php`
- `app/Services/Catalog/CatalogProductService.php`

## Files to Modify
- `routes/web.php`
- `resources/views/layouts/app/sidebar.blade.php`

## Acceptance Criteria
- [ ] Product list renders for authenticated influencer
- [ ] Create and edit forms validate and persist correctly
- [ ] Search/filter/sort run through builder methods (not Blade/controller chains)
- [ ] Archiving/unarchiving works
- [ ] Users cannot access or mutate another user's products
- [ ] Feature tests cover success, validation, authorization, and empty state
