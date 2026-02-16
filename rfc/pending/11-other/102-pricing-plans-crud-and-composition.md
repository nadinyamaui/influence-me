# 102 - Pricing Plans CRUD and Composition

**Labels:** `feature`, `ui`, `pricing`
**Depends on:** #100, #101, #012

## Description

Allow influencers to create reusable plans composed of one or more products, with quantities and optional plan-level bundle pricing.

## Implementation

### Routes
Add authenticated influencer routes:
- `GET /pricing/plans`
- `GET /pricing/plans/create`
- `GET /pricing/plans/{plan}/edit`

### UI
Create Livewire pages for plan list/create/edit.

Plan form fields:
- Name
- Description (optional)
- Currency (default `USD`)
- Bundle price (optional)
- Active toggle

Plan composition section:
- Dynamic rows
- Product select (from influencer-owned active products)
- Quantity
- Optional unit price override
- Row total preview
- Add/remove row actions

### Validation Rules
- At least one plan item required.
- Product IDs must belong to authenticated influencer.
- Quantity must be positive.
- Price overrides must be non-negative.

### Query Layer
`CatalogPlanBuilder` methods:
- `forUser(int $userId)`
- `search(?string $term)`
- `filterByActive(?bool $active)`
- `withItemsCount()`
- `applySort(string $sort)`

### Service Layer
Create `CatalogPlanService` to persist plan and nested item composition transactionally.

## Files to Create
- `app/Livewire/Pricing/Plans/Index.php`
- `app/Livewire/Pricing/Plans/Create.php`
- `app/Livewire/Pricing/Plans/Edit.php`
- `resources/views/pages/pricing/plans/index.blade.php`
- `resources/views/pages/pricing/plans/create.blade.php`
- `resources/views/pages/pricing/plans/edit.blade.php`
- `app/Http/Requests/StoreCatalogPlanRequest.php`
- `app/Services/Catalog/CatalogPlanService.php`

## Files to Modify
- `routes/web.php`
- `resources/views/layouts/app/sidebar.blade.php`

## Acceptance Criteria
- [ ] Influencer can create/edit plans with nested items
- [ ] Plan composition validates ownership and pricing rules
- [ ] Plan list supports search/filter/sort via builder methods
- [ ] Users cannot mutate plans they do not own
- [ ] Feature tests cover success, validation, authorization, and empty state
