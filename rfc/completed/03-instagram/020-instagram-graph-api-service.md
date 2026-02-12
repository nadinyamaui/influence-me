# 020 - Instagram Graph API Service Class

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #003, #002

## Description

Create a service class that encapsulates all Instagram Graph API calls. This service will be used by the sync jobs. Uses Laravel's HTTP client (`Http::`) for all API requests.

## Implementation

### Create `App\Services\InstagramGraphService`

Constructor accepts an `InstagramAccount` model instance.

### Methods

**`getProfile(): array`**
- Endpoint: `GET https://graph.instagram.com/v21.0/me`
- Fields: `id,username,name,biography,profile_picture_url,followers_count,following_count,media_count,account_type`
- Returns: profile data array

**`getMedia(?string $after = null): array`**
- Endpoint: `GET https://graph.instagram.com/v21.0/me/media`
- Fields: `id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count`
- Supports cursor pagination via `after` parameter
- Returns: `['data' => [...], 'paging' => ['cursors' => ['after' => ...]]]`

**`getMediaInsights(string $mediaId): array`**
- Endpoint: `GET https://graph.instagram.com/v21.0/{mediaId}/insights`
- Metrics vary by media type:
  - IMAGE/CAROUSEL: `reach,likes,comments,shares,saved,views,total_interactions`
  - VIDEO/REEL: `views,reach,total_interactions,ig_reels_avg_watch_time,ig_reels_video_view_total_time,reels_skip_rate`
  - STORY: `reach,replies,navigation`
- Returns: metrics array

**`getAudienceDemographics(): array`**
- Endpoint: `GET https://graph.instagram.com/v21.0/me/insights`
- Metrics: `audience_city,audience_country,audience_gender_age`
- Period: `lifetime`
- Returns: demographics data array

**`getStories(): array`**
- Endpoint: `GET https://graph.instagram.com/v21.0/me/stories`
- Fields: `id,caption,media_type,media_url,thumbnail_url,permalink,timestamp`
- Returns: stories array

**`refreshLongLivedToken(): string`**
- Endpoint: `GET https://graph.instagram.com/refresh_access_token`
- Params: `grant_type=ig_refresh_token&access_token={token}`
- Returns: new long-lived token string

### Error Handling
- Create `App\Exceptions\InstagramApiException` with error code and message
- Handle expired tokens (error code 190): throw `InstagramTokenExpiredException`
- Handle generic API errors

## Files to Create
- `app/Services/InstagramGraphService.php`
- `app/Exceptions/InstagramApiException.php`
- `app/Exceptions/InstagramTokenExpiredException.php`

## Acceptance Criteria
- [ ] Service class handles all required API endpoints
- [ ] Pagination handled correctly for media endpoint
- [ ] Error responses throw typed exceptions
- [ ] Token refresh works
- [ ] Unit tests with mocked HTTP responses cover each method
