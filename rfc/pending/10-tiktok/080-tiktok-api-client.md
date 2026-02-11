# 080 - TikTok API Client

**Labels:** `feature`, `tiktok`, `backend`, `integration`
**Depends on:** #079

## Description

Implement a domain-level TikTok client that exposes typed methods for account profile, videos, insights, demographics, and token refresh.

## Implementation

### Create Client
- `App\Clients\TikTokApiClient`
- Depends on `TikTokApiConnector`

### Required Methods
- `getProfile()`
- `getVideos(?string $cursor = null)`
- `getVideoInsights(string $platformMediaId)`
- `getAudienceDemographics()`
- `refreshToken()`

### Response Mapping
- Map raw API payloads into stable app DTO/array shapes

## Files to Create
- `app/Clients/TikTokApiClient.php`

## Acceptance Criteria
- [ ] Client exposes all required domain methods
- [ ] Pagination cursor flow is supported
- [ ] Response mapping is stable and test-covered
- [ ] Client does not include transport-specific logic
