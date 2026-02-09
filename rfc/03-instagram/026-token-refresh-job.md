# 026 - Token Refresh Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #003, #020

## Description

Create a queued job that refreshes Instagram long-lived tokens before they expire. Long-lived tokens last 60 days and must be refreshed before expiry.

## Implementation

### Create `App\Jobs\RefreshInstagramToken`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3
- Retry backoff: `[60, 300, 900]` seconds

### `handle()` Logic
1. Instantiate `InstagramGraphService` with the account
2. Call `refreshLongLivedToken()`
3. Update the account's `access_token` and `token_expires_at` (now + 60 days)
4. Log success

### Error Handling
- If refresh fails, log error and set `last_sync_error` on account
- If token is already expired, mark account as needing re-authentication
- Notify the user (future enhancement) that re-auth is needed

## Files to Create
- `app/Jobs/RefreshInstagramToken.php`

## Acceptance Criteria
- [ ] Job refreshes token successfully
- [ ] New token and expiry stored correctly
- [ ] Expired tokens handled gracefully with error message
- [ ] Feature test verifies token refresh (mocked HTTP)
