# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
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
