# 107 - Pricing, Proposal, and Invoice Test Hardening

**Labels:** `test`, `quality`, `proposals`, `invoicing`, `pricing`
**Depends on:** #068, #101, #102, #103, #104, #105, #106

## Description

Harden test coverage for the full pricing catalog to proposal to invoice workflow, including client portal visibility.

## Implementation

### Feature Coverage
- Pricing products CRUD:
  - render
  - validation
  - authorization
  - empty states
- Pricing plans CRUD with nested composition.
- Proposal pricing step and snapshot persistence.
- Proposal send commercial validation failures/success.
- Invoice import from approved proposal snapshots.
- Client portal invoice commercial context visibility.

### Unit/Service Coverage
- `CatalogProductService`
- `CatalogPlanService`
- `ProposalPricingService`
- `InvoiceFromProposalService`

### Builder Coverage
- catalog product/plan search, filters, sorting, ownership scopes
- proposal line item ownership/sorting scopes

## Files to Create/Modify
- feature tests under `tests/Feature/Pricing/*`, `tests/Feature/Proposals/*`, `tests/Feature/Invoices/*`, `tests/Feature/Portal/*`
- unit tests under `tests/Unit/Services/*`, `tests/Unit/Builders/*`

## Acceptance Criteria
- [ ] New workflows covered for success, validation, and authorization
- [ ] Guard boundaries covered (`web` influencer vs `client` portal)
- [ ] Snapshot behavior verified against catalog changes
- [ ] Existing proposal campaign/schedule tests remain passing
- [ ] Test suite passes within acceptable runtime
