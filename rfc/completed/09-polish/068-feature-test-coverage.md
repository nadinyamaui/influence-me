# 068 - Feature Test Coverage for All Livewire Pages

**Labels:** `testing`, `quality`
**Depends on:** All feature issues

## Description

Ensure comprehensive feature test coverage for every Livewire page and workflow in the application. Each page should have tests covering rendering, actions, authorization, and edge cases.

## Test Files to Create/Verify

### Client Tests
- `tests/Feature/Clients/ClientListTest.php` — list, search, filter, pagination
- `tests/Feature/Clients/ClientCreateTest.php` — form validation, creation
- `tests/Feature/Clients/ClientEditTest.php` — update, delete, authorization
- `tests/Feature/Clients/ClientDetailTest.php` — display, tabs
- `tests/Feature/Clients/ClientPortalInviteTest.php` — invite, revoke

### Content Tests
- `tests/Feature/Content/ContentBrowserTest.php` — gallery, filters, sort
- `tests/Feature/Content/ContentLinkingTest.php` — link, batch link, unlink
- `tests/Feature/Content/ScheduleTest.php` — CRUD, filters

### Proposal Tests
- `tests/Feature/Proposals/ProposalListTest.php` — list, filters
- `tests/Feature/Proposals/ProposalCreateEditTest.php` — CRUD, markdown
- `tests/Feature/Proposals/ProposalSendTest.php` — send workflow
- `tests/Feature/Proposals/ProposalApprovalTest.php` — approve, request changes

### Invoice Tests
- `tests/Feature/Invoices/InvoiceListTest.php` — list, summary cards
- `tests/Feature/Invoices/InvoiceCreateTest.php` — line items, calculations
- `tests/Feature/Invoices/InvoiceSendTest.php` — send workflow
- `tests/Feature/Invoices/InvoiceStatusTransitionsTest.php` — sent, paid, overdue transitions

### Analytics Tests
- `tests/Feature/Analytics/AnalyticsDashboardTest.php` — overview cards, filters
- `tests/Feature/Analytics/ClientAnalyticsTest.php` — campaign metrics

### Portal Tests
- `tests/Feature/Portal/PortalAuthTest.php` — login, logout, guard
- `tests/Feature/Portal/PortalDashboardTest.php` — display, scoping
- `tests/Feature/Portal/PortalProposalsTest.php` — list, detail, approve
- `tests/Feature/Portal/PortalInvoicesTest.php` — list, detail
- `tests/Feature/Portal/PortalAnalyticsTest.php` — display, scoping

### Instagram Tests
- `tests/Feature/Instagram/AccountsPageTest.php` — list, connect, disconnect
- `tests/Feature/Instagram/SyncTest.php` — manual sync trigger

## Testing Patterns
- Use model factories for all test data
- Mock external services (Socialite, Instagram API)
- Test authorization (403 for unauthorized access)
- Test validation (422 for invalid input)
- Use `RefreshDatabase` trait

## Acceptance Criteria
- [x] Every Livewire page has at least one feature test
- [x] Authorization tested for every protected action
- [x] Validation tested for every form
- [x] Edge cases covered (empty states, limits)
- [x] `php artisan test` passes with zero failures
- [x] Test suite runs in under 60 seconds
