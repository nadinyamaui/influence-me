# Influence Me Product Requirements Document (PRD)

## 1. Document Context

- Product: Influence Me
- Source: RFC `000` through `073` in `/rfc`
- Scope: MVP + quality hardening + deployment readiness
- Primary stack assumptions from RFCs: Laravel + Livewire + Flux UI, Instagram Graph API, Stripe, Redis-backed queues

## 2. Product Vision

Influence Me helps influencers run the business side of content operations in one platform: connect Instagram data, manage clients, prepare proposals, issue invoices, collect payments, and report outcomes.

## 3. Primary Personas

- Influencer (primary user)
- Client user invited into portal by influencer

## 4. Product Goals

- Centralize influencer operations across content, clients, proposals, invoicing, and analytics
- Reduce operational overhead through automation (sync jobs, scheduling, reminders)
- Improve revenue flow with faster proposal approval and payment collection
- Provide transparent client visibility through a scoped portal

## 5. Explicit Non-Goals (MVP)

- Native Instagram content publishing automation is not defined in the RFC set
- Multi-role team collaboration beyond influencer + client user is not defined
- Annual billing/subscription management implementation is not defined (pricing is marketing-facing in RFC `000`)

## 6. Functional Requirements by Epic

### 6.1 Marketing and Conversion (RFC `000`)

- Public landing page at `/` accessible to guests and authenticated users
- Required sections in order: hero, social proof, benefits, features, how it works, pricing, FAQ, final CTA
- Mandatory CTAs to login/register in header, hero, and final CTA
- Pricing tiers by follower count:
  - Free: `<1,000` ($0)
  - Growth: `1,000-10,000` ($25)
  - Creator: `10,001-99,999` ($49)
  - Scale: `100,000-300,000` ($75)
  - Pro: `300,000-600,000` ($100)
  - Enterprise: `>600,000` (contact)

### 6.2 Data Foundation and Access Control (RFC `001-013`)

- Build full MVP schema (users, instagram accounts/media, demographics, clients, client users, proposals, invoices, items, campaign media, scheduled posts)
- Enforce backed string enums for status/type consistency (RFC `002`)
- Implement model relationships/factories/seed paths for all domain models
- Add policy coverage to enforce per-owner data access and client scoping
- Build base sidebar navigation structure for all major product areas

### 6.3 Authentication and Identity (RFC `014-019`)

- Configure Meta app setup documentation and OAuth integration setup
- Implement influencer login using Instagram OAuth + long-lived token exchange
- Remove/disable influencer email-password registration and reset flows
- Keep optional two-factor authentication support
- Add separate `client` guard/provider/password broker for client portal users
- Implement client portal login/logout at `/portal/login`

### 6.4 Instagram Data Platform (RFC `020-030`)

- Create Instagram Graph service wrapper for profile/media/insights/stories/demographics/token refresh
- Add typed exception handling for API failures and token-expired cases
- Implement queued sync jobs:
  - profile sync
  - media sync (paginated, idempotent)
  - media insights sync
  - stories sync
  - audience demographics sync
  - token refresh
- Add sync orchestrator chaining jobs and updating sync state
- Add scheduler cadence:
  - full sync every 6 hours
  - profile/insights hourly
  - token refresh daily for expiring tokens
- Build Instagram accounts UI:
  - list connected accounts
  - connect/disconnect additional accounts
  - set primary account
  - trigger manual sync and show progress/status polling

### 6.5 Client Management and Portal Foundation (RFC `031-037`)

- Influencer client CRUD:
  - list with search/filter/pagination
  - create/edit/delete
  - detail page with tab scaffold
- Build dedicated client portal layout and dashboard
- Invite/revoke portal access from client detail page
- Send welcome credentials via email for invited client users

### 6.6 Content and Campaign Operations (RFC `038-042`)

- Build content browser gallery from synced Instagram media with filters/sort/pagination
- Add media detail modal with metrics and deep link to Instagram post
- Link/unlink media to clients, including batch linking and optional campaign metadata
- Populate client detail content tab grouped by campaign
- Build schedule timeline page at `/schedule`:
  - chronological grouped list
  - filters (status/client/account/date range)
  - CRUD via modal
  - status updates (`planned`, `published`, `cancelled`)

### 6.7 Proposal Workflows (RFC `043-048`)

- Influencer proposal list with status/client filtering
- Create/edit proposals with markdown content and preview
- Proposal detail page with status-aware actions
- Send-to-client workflow:
  - status transition to `sent`
  - timestamping + email dispatch
- Client portal proposals:
  - only relevant statuses visible
  - scoped detail view
- Client response actions:
  - approve
  - request changes with revision notes
  - notify influencer via email

### 6.8 Invoicing and Payments (RFC `049-057`)

- Invoice list with summaries and status filtering
- Invoice creation with dynamic line items and automatic totals/tax
- Invoice preview/detail with draft-only edit/delete constraints
- Stripe backend service integration
- Payment link generation from invoice detail
- Stripe webhook handling (`/webhooks/stripe`) for payment completion
- Send invoice workflow with resend support
- Client portal invoice list/detail/pay-now paths
- Daily overdue detection command and optional reminder notifications

### 6.9 Analytics (RFC `058-066`)

- Analytics dashboard with overview cards and filters
- Charts:
  - audience growth
  - engagement trend
  - content type breakdown
  - demographics (age/gender/city/country)
- Best-performing content section
- Per-post comparison against account averages
- Client detail analytics tab based on linked campaign content
- Client portal analytics page scoped to authenticated client-linked data

### 6.10 Quality, Hardening, and Operations (RFC `067-073`)

- Replace old auth tests with Instagram OAuth-focused coverage
- Achieve broad feature coverage across Livewire pages and workflows
- Responsive design validation (mobile/tablet/desktop)
- Consistent loading and empty states across lists/forms
- Improve error handling and monitoring with dedicated log channels
- Security hardening:
  - rate limiting
  - token encryption
  - XSS-safe markdown rendering
  - strict validation and mass-assignment safety
- Deployment documentation and environment variable inventory

## 7. Core Data Model and Relationships

Primary relationships expected by RFC set:

- `User hasMany InstagramAccount`
- `User hasMany Client`
- `User hasMany Proposal`
- `User hasMany Invoice`
- `User hasMany ScheduledPost`
- `User belongsTo InstagramAccount` as primary account (nullable)
- `InstagramAccount hasMany InstagramMedia`
- `InstagramAccount hasMany AudienceDemographic`
- `Client hasOne/hasMany ClientUser` (portal identity)
- `Client hasMany Proposal`
- `Client hasMany Invoice`
- `Client belongsToMany InstagramMedia` through `campaign_media`
- `Proposal belongsTo User and Client`
- `Invoice belongsTo User and Client; Invoice hasMany InvoiceItem`
- `ScheduledPost belongsTo User, optional Client, and InstagramAccount`

## 8. State Machines and Lifecycle Rules

### 8.1 Proposal Status

- `draft -> sent`
- `sent -> approved`
- `sent -> revised` (client request changes)
- `revised -> sent` (re-send path)

### 8.2 Invoice Status

- `draft -> sent`
- `sent -> paid` (Stripe webhook)
- `sent -> overdue` (scheduled overdue detection)

### 8.3 Scheduled Post Status

- `planned -> published`
- `planned -> cancelled`

### 8.4 Sync Status

- `idle -> syncing`
- `syncing -> idle | failed`

## 9. Integrations

### 9.1 Instagram (Meta Graph API)

- OAuth and token lifecycle management
- API rate limit tracking per account
- Robust error typing and retry/backoff behaviors

### 9.2 Stripe

- Payment link generation for invoices
- Signed webhook verification
- Payment completion updates invoice and sends notifications

### 9.3 Email

Mandatory mail flows include:

- Proposal sent
- Proposal approved / revision requested
- Invoice sent
- Payment received
- Client portal invitation

## 10. Background Jobs and Schedules

Required scheduled operations:

- Instagram full sync: every 6 hours
- Instagram profile/insights refresh: hourly
- Token refresh: daily for near-expiry tokens
- Overdue invoice scan: daily at 9 AM
- Daily follower snapshots for analytics trends

Queue expectations:

- Instagram sync uses dedicated queue (`instagram-sync`) and Horizon config
- Long-running jobs must handle retries/timeouts safely

## 11. Security and Compliance Requirements

- Enforce per-record ownership policies for influencer and portal users
- Keep influencer and client sessions isolated by guard
- Exclude Stripe webhook route from CSRF while enforcing signature validation
- Encrypt tokens at rest
- Validate all form input with explicit constraints and max lengths
- Ensure markdown rendering is sanitized
- Add rate limits for portal login and webhook endpoints as specified

## 12. UX and Accessibility Requirements

- All major pages must include empty states and loading states
- Responsive behavior required for 375px, 768px, and 1920px targets
- Avoid horizontal overflow except intentional table scroll
- Maintain touch-target/readability quality for mobile

## 13. Testing and Acceptance Strategy

Test strategy across RFCs:

- Unit tests for enum definitions and service logic
- Feature tests for every Livewire page/workflow
- Authorization and guard boundary tests for protected actions
- Validation tests for all forms
- Mocked external integration tests (Socialite, Instagram API, Stripe)
- End-to-end workflow tests for proposal/invoice/schedule state transitions

Release acceptance:

- All relevant RFC acceptance criteria pass
- Full test suite passes with no failures
- Coverage includes edge cases: empty states, unauthorized access, invalid input, and duplicate/link constraints

## 14. Delivery Plan and Milestones

Milestone sequence derived from dependencies:

1. `000-013`: foundation schema/models/policies/navigation
2. `014-019`: dual-auth architecture
3. `020-030`: Instagram integration + accounts management
4. `031-037`: client management + portal core
5. `038-042`: content operations + schedule timeline
6. `043-048`: proposals and client response workflows
7. `049-057`: invoicing, Stripe, overdue automation
8. `058-066`: analytics for influencer and portal
9. `067-073`: quality, security, and deployment readiness

## 15. Risks and Implementation Notes

- Instagram API limits and token-expiry handling can disrupt data freshness if retries/backoff are weak
- Cross-guard data leakage is a critical risk; all portal queries must be client-scoped
- Stripe webhook robustness (signature validation + idempotence) is required to avoid payment-state errors
- Large analytics queries require indexing and scoped aggregation to maintain performance

## 16. RFC Traceability Index

- `000` Public Landing Page
- `001` Core Database Schema Migrations
- `002` PHP Enums for Status and Type Fields
- `003` InstagramAccount Model, Factory, and Seeder
- `004` InstagramMedia Model and Factory
- `005` AudienceDemographic Model and Factory
- `006` Client Model and Factory
- `007` ClientUser Model and Factory
- `008` Proposal Model and Factory
- `009` Invoice and InvoiceItem Models and Factories
- `010` ScheduledPost Model and Factory
- `011` CampaignMedia Pivot Configuration
- `012` Authorization Policies for All Models
- `013` Sidebar Navigation Structure
- `014` Meta App Setup Documentation
- `015` Instagram Socialite Service Configuration
- `016` Instagram OAuth Login Flow
- `017` Remove Email/Password Authentication
- `018` Client Authentication Guard Setup
- `019` Client Portal Login Page
- `020` Instagram Graph API Service Class
- `021` Sync Instagram Profile Job
- `022` Sync Instagram Media Job
- `023` Sync Media Insights Job
- `024` Sync Instagram Stories Job
- `025` Sync Audience Demographics Job
- `026` Token Refresh Job
- `027` Sync Orchestrator Job and Scheduled Tasks
- `028` Instagram Accounts List Page
- `029` Connect and Disconnect Instagram Accounts
- `030` Manual Sync Trigger and Status UI
- `031` Client List Page
- `032` Client Create Form
- `033` Client Edit Form
- `034` Client Detail Page
- `035` Client Portal Layout
- `036` Invite Client to Portal
- `037` Client Portal Dashboard
- `038` Content Browser Gallery Page
- `039` Content Detail Modal
- `040` Link Content to Client
- `041` Client Detail Linked Content Tab
- `042` Schedule Timeline Page
- `043` Proposal List Page
- `044` Proposal Create and Edit Pages
- `045` Proposal Preview/Detail Page
- `046` Send Proposal to Client
- `047` Client Portal Proposals List and Detail
- `048` Proposal Approval and Revision Workflow
- `049` Invoice List Page
- `050` Invoice Create with Dynamic Line Items
- `051` Invoice Preview/Detail Page
- `052` Stripe Service Integration
- `053` Stripe Payment Link Generation UI
- `054` Stripe Webhook Handler
- `055` Send Invoice to Client
- `056` Client Portal Invoices
- `057` Overdue Invoice Detection
- `058` Analytics Dashboard Overview Cards
- `059` Audience Growth Chart
- `060` Engagement Rate Trend Chart
- `061` Best Performing Content Section
- `062` Content Type Breakdown Chart
- `063` Per-Post Analytics Detail View
- `064` Campaign/Client Analytics Tab
- `065` Audience Demographics Charts
- `066` Client Portal Analytics Page
- `067` Update Authentication Tests for Instagram OAuth
- `068` Feature Test Coverage for All Livewire Pages
- `069` Responsive Design Pass
- `070` Loading States and Empty States
- `071` Error Handling and Monitoring
- `072` Security Hardening
- `073` Deployment Documentation
