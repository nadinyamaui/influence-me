# 025 - Sync Audience Demographics Job

**Labels:** `feature`, `instagram`, `backend`
**Depends on:** #005, #020

## Description

Create a queued job that fetches audience demographics (age, gender, city, country breakdowns) from the Instagram Graph API and stores them in the `audience_demographics` table.

## Implementation

### Create `App\Jobs\SyncAudienceDemographics`

- Implements `ShouldQueue`
- Constructor accepts `InstagramAccount $account`
- Queue: `instagram-sync`
- Max tries: 3

### `handle()` Logic
1. Instantiate `InstagramGraphService` with the account
2. Call `getAudienceDemographics()`
3. Parse the response — Instagram returns data like:
   - `audience_gender_age`: `{"F.18-24": 0.12, "M.25-34": 0.25, ...}`
   - `audience_city`: `{"London, England": 0.08, ...}`
   - `audience_country`: `{"US": 0.35, "GB": 0.12, ...}`
4. For each demographic entry, create an `AudienceDemographic` record:
   - Set `type` from `DemographicType` enum
   - Set `dimension` (the label, e.g., "18-24", "Male", "London")
   - Set `value` (the percentage)
   - Set `recorded_at` to now
5. Delete old demographic records for this account before inserting new ones (demographics are a snapshot, not historical by default)

### Notes
- Audience demographics require a minimum of 100 followers
- This API requires `instagram_manage_insights` permission

## Files to Create
- `app/Jobs/SyncAudienceDemographics.php`

## Acceptance Criteria
- [ ] Job fetches and parses demographics data
- [ ] Records created with correct type, dimension, value
- [ ] Old records replaced with fresh snapshot
- [ ] Handles accounts with < 100 followers gracefully (skip)
- [ ] Feature test verifies demographics sync (mocked HTTP)
