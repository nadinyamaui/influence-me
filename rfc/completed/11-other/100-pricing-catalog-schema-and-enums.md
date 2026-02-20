# 100 - Pricing Catalog Schema and Enums

**Labels:** `feature`, `foundation`, `proposals`, `invoicing`
**Depends on:** #001, #002, #012

## Description

Add schema and enums for influencer pricing products/plans and proposal pricing snapshots.

## Implementation

### Enums
- Add `App\Enums\PlatformType` with values: `instagram`, `tiktok`, `snapchat`, `youtube`, `twitch`, `kick`.
- Add `App\Enums\CatalogSourceType` with values: `product`, `plan`, `custom`.
- Add `App\Enums\BillingUnitType` with values: `deliverable`, `package`.
- Keep enum UI metadata (`label()`, badge helpers where relevant) on enum classes.

### Schema
Create tables:
- `catalog_products`
- `catalog_plans`
- `catalog_plan_items`
- `proposal_line_items`

`catalog_products` columns:
- `id`
- `user_id` (foreign, cascade delete)
- `name` (string)
- `platform` (string enum-backed)
- `media_type` (string enum-backed nullable; reuse `MediaType`)
- `billing_unit` (string enum-backed)
- `base_price` (decimal 10,2)
- `currency` (char 3 default `USD`)
- `is_active` (boolean default true)
- timestamps

`catalog_plans` columns:
- `id`
- `user_id` (foreign, cascade delete)
- `name` (string)
- `description` (text nullable)
- `bundle_price` (decimal 10,2 nullable)
- `currency` (char 3 default `USD`)
- `is_active` (boolean default true)
- timestamps

`catalog_plan_items` columns:
- `id`
- `catalog_plan_id` (foreign, cascade delete)
- `catalog_product_id` (foreign, restrict delete)
- `quantity` (decimal 8,2)
- `unit_price_override` (decimal 10,2 nullable)
- timestamps

`proposal_line_items` columns:
- `id`
- `proposal_id` (foreign, cascade delete)
- `source_type` (string enum-backed)
- `source_id` (unsignedBigInteger nullable)
- `name_snapshot` (string)
- `description_snapshot` (text nullable)
- `platform_snapshot` (string nullable)
- `media_type_snapshot` (string nullable)
- `quantity` (decimal 8,2)
- `unit_price` (decimal 10,2)
- `line_total` (decimal 10,2)
- `sort_order` (unsignedInteger default 0)
- timestamps

Platform deliverable rule:
- `media_type` and `media_type_snapshot` may be null for platform deliverables that do not map to `post`, `reel`, or `story`.

### Models and Builders
Create models + typed builders:
- `CatalogProduct` + `CatalogProductBuilder`
- `CatalogPlan` + `CatalogPlanBuilder`
- `CatalogPlanItem` + `CatalogPlanItemBuilder`
- `ProposalLineItem` + `ProposalLineItemBuilder`

Add relationships:
- `User::catalogProducts()` and `User::catalogPlans()`
- `Proposal::lineItems()`
- `CatalogPlan::items()`

### Authorization and Ownership
- Scope all create/read/update/delete by authenticated influencer ownership.
- Add policies for catalog entities.

## Files to Create
- `app/Enums/PlatformType.php`
- `app/Enums/CatalogSourceType.php`
- `app/Enums/BillingUnitType.php`
- migrations for catalog tables and `proposal_line_items`
- `app/Models/CatalogProduct.php`
- `app/Models/CatalogPlan.php`
- `app/Models/CatalogPlanItem.php`
- `app/Models/ProposalLineItem.php`
- `app/Builders/CatalogProductBuilder.php`
- `app/Builders/CatalogPlanBuilder.php`
- `app/Builders/CatalogPlanItemBuilder.php`
- `app/Builders/ProposalLineItemBuilder.php`
- policy classes for catalog entities

## Files to Modify
- `app/Models/User.php`
- `app/Models/Proposal.php`
- `app/Providers/AppServiceProvider.php` (policy registration if needed)

## Acceptance Criteria
- [ ] Migrations run and rollback cleanly
- [ ] Enums expose values and labels for UI/validation reuse
- [ ] New models and typed builders compile with relationships
- [ ] Ownership scoping is enforced across catalog and proposal line items
- [ ] Unit tests cover enum values and model relationships
