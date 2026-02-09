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
