# 079 - TikTok API Connector

**Labels:** `feature`, `tiktok`, `backend`, `integration`
**Depends on:** #075

## Description

Implement a transport-level connector for TikTok API HTTP requests, retries, auth headers, and low-level response normalization.

## Implementation

### Create Connector
- `App\Connectors\TikTokApiConnector`
- Encapsulate base URL, auth header construction, timeout, retry policy
- Define generic request methods used by client layer

### Error Mapping
- Map HTTP/network errors to typed connector exceptions
- Include rate-limit and token-expiry signal support

## Files to Create
- `app/Connectors/TikTokApiConnector.php`
- `app/Exceptions/TikTokApiException.php`
- `app/Exceptions/TikTokTokenExpiredException.php`

## Acceptance Criteria
- [ ] Connector centralizes request transport concerns
- [ ] Retry and timeout policy is implemented
- [ ] Typed exceptions are thrown for API failures
- [ ] Unit tests verify request composition and error mapping
