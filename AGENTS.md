# Influence Me Agent Delivery Guide

This file is generated from the RFC set in `/rfc` (RFC `000` through `092`).
Use it as the execution contract for AI agents working in this repository.

## Source of Truth

- Primary requirements: all files under `/rfc`
- Canonical roadmap order: RFC numeric order, constrained by each RFC's `Depends on`
- Completion definition: every relevant acceptance criterion in the target RFC(s) is satisfied and tested

## Product Summary

Influence Me is an influencer operating system that centralizes:

- Instagram account connectivity and sync
- TikTok account connectivity and sync
- Content browsing and client campaign linking
- Content scheduling timeline
- Client CRM and client portal access
- Proposal authoring, sending, and approval workflows
- Invoice generation, Stripe payment collection, and overdue tracking
- Analytics for influencer and client portal views

## Personas

- Influencer (primary `web` guard): manages accounts, content, clients, proposals, invoices, analytics
- Client user (`client` guard): views and responds to proposals, views/pays invoices, views scoped analytics

## Non-Negotiable Product Rules

- TikTok is an additional connected platform and must not replace Instagram-first influencer authentication
- Client portal uses separate `client` guard with isolated session/auth flows (RFC `018`, `019`)
- Data ownership is strict: influencers see only their data; clients see only their client-scoped data (RFC `012`)
- `ScheduledPost` is planning/tracking CRUD in MVP, not direct Instagram auto-publishing
- External integrations (Instagram Graph API, TikTok API, Stripe) must be wrapped in service classes with typed error handling

## Domain Model Baseline (RFC 001/002)

Core entities:

- `User`
- `InstagramAccount`
- `InstagramMedia`
- `AudienceDemographic`
- `TikTokAccount`
- `TikTokMedia`
- `TikTokAudienceDemographic`
- `Client`
- `ClientUser`
- `Proposal`
- `Invoice`
- `InvoiceItem`
- `ScheduledPost`
- `campaign_media` pivot

Required enums:

- `MediaType`: `post`, `reel`, `story`
- `ClientType`: `brand`, `individual`
- `ProposalStatus`: `draft`, `sent`, `approved`, `rejected`, `revised`
- `InvoiceStatus`: `draft`, `sent`, `paid`, `overdue`, `cancelled`
- `ScheduledPostStatus`: `planned`, `published`, `cancelled`
- `DemographicType`: `age`, `gender`, `city`, `country`
- `AccountType`: `business`, `creator`
- `SyncStatus`: `idle`, `syncing`, `failed`

## Required State Transitions

- Proposal: `Draft/Revised -> Sent -> Approved|Rejected|Revised`
- Invoice: `Draft -> Sent -> Paid` and `Sent -> Overdue` (scheduled detection)
- Scheduled post: `Planned -> Published|Cancelled`
- Instagram sync status: `Idle -> Syncing -> Idle|Failed`
- TikTok sync status: `Idle -> Syncing -> Idle|Failed`

## Integrations and Background Work

Instagram integration requirements:

- OAuth login and account linking via Socialite + Meta token exchange
- Graph API service methods for profile, media, insights, stories, demographics, token refresh
- Queued jobs for profile/media/insights/stories/demographics/token refresh
- Orchestrator chain and scheduler cadence:
  - full sync every 6 hours
  - profile + insights hourly
  - token refresh daily for expiring tokens

TikTok integration requirements:

- Account linking via TikTok OAuth for authenticated influencer users
- Client/connector/service abstractions for profile, media, insights, demographics, token refresh
- Queued jobs for profile/media/insights/demographics/token refresh
- Orchestrator chain and scheduler cadence:
  - full sync every 6 hours
  - profile + insights hourly
  - token refresh daily for expiring tokens

Stripe integration requirements:

- Stripe service for payment link generation and webhook verification
- Webhook endpoint at `/webhooks/stripe` (CSRF excluded)
- `checkout.session.completed` marks invoice paid and triggers influencer notification

Additional scheduler requirements:

- Overdue invoice detection daily at 9 AM
- Audience/follower snapshot jobs for analytics trending

## Delivery Epics and RFC Map

- `000`: Public marketing landing page with pricing and auth CTAs
- `001-013`: Foundation (schema, enums, models, policies, base navigation)
- `014-019`: Authentication and dual-guard setup
- `020-030`: Instagram services, sync jobs, orchestration, accounts UI
- `031-037`: Client management and client portal foundation
- `038-042`: Content gallery, linking, client content tab, schedule timeline
- `043-048`: Proposal CRUD, send flow, client approval/revision workflow
- `049-057`: Invoicing CRUD, Stripe payment link/webhook, overdue handling
- `058-066`: Analytics dashboard + client-scoped analytics
- `067-073`: Test hardening, responsive/UX polish, security, deployment docs
- `074-092`: TikTok platform integration (setup, models, connector/client/service, sync jobs, accounts UI, content + analytics integration)

## Implementation Expectations For Agents

For every task, agents must:

- Link work to explicit RFC ID(s)
- Respect dependency graph before implementing downstream features
- Reuse existing models/enums/statuses instead of introducing new variants
- Enforce policy and guard constraints on every protected action/page
- Keep controllers thin: request validation/session intent + response orchestration only; business logic and external API logic must live in service classes
- For third-party APIs, use a `client` + `connector` structure (connector handles HTTP transport/endpoints, client exposes domain methods) so services remain API-agnostic
- Mock Instagram, TikTok, Socialite, and Stripe in tests; do not rely on live APIs
- Cover success, validation, authorization, and empty-state paths

## Decoupling Architecture Rules

All new and modified code must preserve strict layer boundaries:

- `Controllers/Livewire components`: HTTP/UI orchestration only (request parsing, auth checks, response/redirect composition)
- `Services`: use-case/business workflow orchestration only
- `Clients`: third-party/domain adapters (typed domain-level API calls)
- `Connectors`: raw transport concerns (HTTP base URL, headers, retries/timeouts, low-level request methods)
- `Models`: persistence and relationships only (no external API calls)
- `Jobs`: async orchestration that delegates to services/clients (no inline business logic duplication)

Required dependency direction:

- UI layer -> Services -> Clients -> Connectors
- Never invert this direction
- Shared logic must be extracted downward (never copied sideways across controllers/jobs/components)

Hard constraints:

- No direct `Http::` usage in controllers, Livewire components, models, policies, or form requests
- No direct SDK/facade calls for external APIs in controllers (must route through service/client abstractions)
- No business rule branching duplicated between controller and service
- No persistence side effects hidden inside connectors
- Do not use `data_get` for object property traversal; use nullsafe property access (`$object?->property?->property`) instead
- Do not use custom normalize helper functions for request/session input; use Laravel validation rules (`$request->validate()` or Form Requests) and explicit defaults instead
- Do not use `isset()` for value retrieval/defaulting; use null coalescing (`??`) with explicit defaults instead
- Do not add method-level docblocks; methods should not include PHPDoc comments unless explicitly required by a framework or tooling constraint
- Do not add inline comments inside function bodies; function code should be self-explanatory without internal comments

Testing requirements for decoupling:

- Feature tests cover behavior at controller/page boundary
- Unit/service tests cover workflow rules and guard/ownership rules
- Client tests mock transport responses and verify mapping/error handling
- Connector tests (if added) cover request composition only

## GitHub Tracking Conventions

- Issue titles for RFC work must use: `RFC NNN: Title`
- RFC issues must embed the full RFC markdown in the issue body
- Do not open RFC issues that only reference `rfc/*.md` file paths
- PR descriptions must include exactly one native GitHub issue link line: `Closes #<issue-number>`
- Do not use issue comments to link PRs to issues

## Testing Expectations

Minimum by feature area:

- Livewire page rendering + action tests
- Authorization/guard boundary tests (`web` vs `client`)
- Validation tests for every form workflow
- Workflow transition tests (proposal, invoice, schedule)
- Integration adapter tests with mocked HTTP/Stripe
- Scheduler/queue dispatch behavior tests for background jobs

Global quality targets (RFC `068`):

- Every Livewire page has feature coverage
- Protected actions include authorization tests
- Test suite passes fully and remains performant

## Definition of Done

A change is done only when:

- Target RFC acceptance criteria are fully met
- Related routes, UI navigation, and auth middleware are consistent
- Required tests are added/updated and passing
- Error handling and logging requirements for external dependencies are met
- No contradictions are introduced with prior RFC constraints

## Full RFC Inventory

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
- `074` TikTok Developer App Setup Documentation
- `075` TikTok Socialite Service Configuration
- `076` TikTokAccount Model, Factory, and Seeder
- `077` TikTokMedia Model and Factory
- `078` TikTokAudienceDemographic Model and Factory
- `079` TikTok API Connector
- `080` TikTok API Client
- `081` TikTok Sync Service Class
- `082` Sync TikTok Profile Job
- `083` Sync TikTok Media Job
- `084` Sync TikTok Media Insights Job
- `085` Sync TikTok Audience Demographics Job
- `086` TikTok Token Refresh Job
- `087` TikTok Sync Orchestrator and Scheduled Tasks
- `088` TikTok Accounts List Page
- `089` Connect and Disconnect TikTok Accounts
- `090` TikTok Manual Sync Trigger and Status UI
- `091` TikTok Content Browser Integration
- `092` TikTok Analytics Dashboard Integration
