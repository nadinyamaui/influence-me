# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- RFC 022: added `App\Jobs\SyncInstagramMedia` queued job with `instagram-sync` queue configuration, pagination-aware media syncing, API-to-enum media type mapping, and idempotent `updateOrCreate` persistence.
- RFC 022: added Instagram Graph API media adapter and typed exceptions in `app/Services/InstagramGraphService.php`, `app/Exceptions/InstagramApiException.php`, and `app/Exceptions/InstagramTokenExpiredException.php` for mocked HTTP-backed media sync calls.
- RFC 022: added feature coverage in `tests/Feature/Jobs/SyncInstagramMediaTest.php` for pagination sync behavior, media-type mapping, idempotent updates, and job queue settings.
- RFC 002: added backed string enums in `app/Enums/` for media, clients, proposals, invoices, scheduled posts, demographics, account type, and sync status.
- RFC 002: added unit coverage in `tests/Unit/EnumsTest.php` to verify enum cases and lowercase backed values.
- RFC 003: added `InstagramAccount` model with guarded attributes, enum/date/boolean/encrypted casts, and influencer/media/demographics relationships.
- RFC 003: added `InstagramAccountFactory` with default account generation plus `primary`, `business`, `creator`, and `tokenExpired` states.
- RFC 003: added influencer relationship helpers on `User` (`instagramAccounts`, `primaryInstagramAccount`) and seeded a default linked primary Instagram account.
- RFC 003: added feature coverage in `tests/Feature/Models/InstagramAccountTest.php` for factory defaults/states, encrypted token persistence, and relationship behavior.
- RFC 004: added `InstagramMedia` model with guarded attributes, enum/date/decimal casts, and `instagramAccount`/`clients` relationships.
- RFC 004: added `InstagramMediaFactory` with realistic default generation and `post`, `reel`, `story`, and `highEngagement` states.
- RFC 004: added feature coverage in `tests/Feature/Models/InstagramMediaTest.php` for default records, factory state behavior, and relationship wiring.
- RFC 005: added `AudienceDemographic` model with guarded attributes, enum/decimal/datetime casts, and `instagramAccount` relationship.
- RFC 005: added `AudienceDemographicFactory` with default demographic generation and `age`, `gender`, `city`, and `country` states.
- RFC 005: added feature coverage in `tests/Feature/Models/AudienceDemographicTest.php` for default records, state behavior, and relationship wiring.
- RFC 006: expanded `Client` model with casts and full influencer/portal/proposal/invoice/campaign-media relationships and added `User::clients()`.
- RFC 006: added `ClientFactory` with realistic defaults and `brand`/`individual` states.
- RFC 006: added feature coverage in `tests/Feature/Models/ClientTest.php` for default records, states, and relationship definitions.
- RFC 007: added `ClientUser` authenticatable model with factory trait, notifiable trait, fillable/hidden attributes, hashed password cast, and `client` relationship.
- RFC 007: added `ClientUserFactory` with default credential generation and automatic client linkage.
- RFC 007: added feature coverage in `tests/Feature/Models/ClientUserTest.php` for hashed password persistence, relationship wiring, and session guard authentication behavior.
- RFC 008: added `Proposal` model with guarded attributes, enum/datetime casts, and `user`/`client` relationships.
- RFC 008: added `ProposalFactory` with markdown defaults and `draft`, `sent`, `approved`, `rejected`, and `revised` states.
- RFC 008: added `User::proposals()` relationship for influencer proposal ownership.
- RFC 008: added feature coverage in `tests/Feature/Models/ProposalTest.php` for defaults, factory states, and relationship return types.
- RFC 009: added `Invoice` and `InvoiceItem` models with guarded attributes, required enum/date/decimal/datetime casts, and influencer/client/item relationships.
- RFC 009: added `Invoice::calculateTotals()` and automatic ID-based invoice numbering (uses the persisted invoice ID as `invoice_number`).
- RFC 009: added `InvoiceFactory` default/state generation (`draft`, `sent`, `paid`, `overdue`) and `InvoiceItemFactory` realistic line-item generation.
- RFC 009: added `User::invoices()` relationship for influencer-owned invoices.
- RFC 009: added feature coverage in `tests/Feature/Models/InvoiceTest.php` for factory defaults/states, relationship typing, totals calculation, and invoice numbering behavior.
- RFC 010: added `ScheduledPost` model with guarded attributes, required enum/datetime casts, and `user`/`client`/`instagramAccount` relationships.
- RFC 010: added `ScheduledPostFactory` with default planned future posts and `planned`, `published`, and `cancelled` states.
- RFC 010: added `User::scheduledPosts()` relationship for influencer-owned scheduled posts.
- RFC 010: added feature coverage in `tests/Feature/Models/ScheduledPostTest.php` for factory defaults/states, relationship typing, nullable client linkage, and user relationship behavior.
- RFC 011: configured `campaign_media` pivot relationships on `Client::instagramMedia()` and `InstagramMedia::clients()` with pivot fields and timestamps.
- RFC 011: added feature coverage in `tests/Feature/Models/CampaignMediaPivotTest.php` for pivot data access, timestamp tracking, and attach/detach behavior from both relationship sides.
- RFC 012: added model policies in `app/Policies/` for `InstagramAccount`, `Client`, `Proposal`, `Invoice`, `ScheduledPost`, and `InstagramMedia`.
- RFC 012: enforced ownership and workflow authorization rules including proposal send restrictions, draft-only invoice edits/deletes, and prevention of deleting a user's last Instagram account.
- RFC 012: added feature coverage in `tests/Feature/Authorization/ModelPoliciesTest.php` for policy auto-discovery, authorized/unauthorized outcomes across policy methods, and HTTP `403` responses for denied access.
- RFC 013: expanded influencer app navigation in `resources/views/layouts/app/sidebar.blade.php` with the Platform, Manage, and Instagram groups and all MVP placeholder links.
- RFC 013: updated `resources/views/layouts/app/header.blade.php` (desktop and mobile variants) to mirror the same RFC 013 navigation structure and removed starter external links.
- RFC 013: added feature coverage in `tests/Feature/NavigationStructureTest.php` to verify grouped navigation labels, dashboard route wiring, placeholder links, and removal of external links.
- RFC 014: added `docs/meta-app-setup.md` with end-to-end Meta Developer App setup, permissions, OAuth callback configuration, token lifecycle, app review guidance, and troubleshooting for Instagram Graph API onboarding.
- RFC 014: updated `.env.example` with `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, and `INSTAGRAM_REDIRECT_URI` defaults required by Instagram OAuth configuration.
- RFC 015: added Instagram Socialite service configuration in `config/services.php` using `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, and `INSTAGRAM_REDIRECT_URI`.
- RFC 015: registered the Socialite Providers Instagram listener in `app/Providers/AppServiceProvider.php` so `Socialite::driver('instagram')` resolves correctly.
- RFC 015: added feature coverage in `tests/Feature/Auth/InstagramSocialiteConfigurationTest.php` for listener registration and Instagram driver resolution.
- RFC 018: added a dedicated `client` auth guard, `clients` user provider, and `clients` password broker in `config/auth.php` for client-portal session isolation.
- RFC 018: added `routes/portal.php` with `guest:client` and `auth:client` middleware groups and loaded it from `routes/web.php`.
- RFC 018: added feature coverage in `tests/Feature/Auth/ClientGuardSetupTest.php` and updated `tests/Feature/Models/ClientUserTest.php` to validate real guard wiring and preserve `web` guard behavior.
- RFC 019: added `PortalAuthController` and `PortalLoginRequest` to implement client-guard login/logout workflows, validation, session regeneration, and auth failure handling for `/portal/login`.
- RFC 019: added `resources/views/pages/portal/login.blade.php` using the shared auth layout with client-portal-specific copy and only email/password login controls.
- RFC 019: added portal auth routes in `routes/portal.php` (`portal.login`, `portal.login.store`, `portal.logout`) and enforced a dedicated `client-portal-login` rate limit of 5 requests/minute per IP.
- RFC 019: added feature coverage in `tests/Feature/Auth/ClientPortalLoginTest.php` for login page rendering, successful/failed login, logout, rate limiting, and `web`/`client` guard isolation.
- RFC 014: added `docs/meta-app-setup.md` with end-to-end Meta Developer App setup, permissions, OAuth callback configuration, token lifecycle, app review guidance, and troubleshooting for Instagram Graph API onboarding.
- RFC 014: updated `.env.example` with `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, and `INSTAGRAM_REDIRECT_URI` defaults required by Instagram OAuth configuration.
