# 014 - Meta App Setup Documentation

**Labels:** `documentation`
**Depends on:** None

## Description

Create a step-by-step setup guide for configuring the Meta Developer App required for Instagram Graph API access. The user does not have a Meta App yet, so this must be comprehensive.

## Document to Create: `docs/meta-app-setup.md`

### Sections to Cover

1. **Prerequisites**
   - Instagram Business or Creator account (not Personal)
   - Facebook Page connected to the Instagram account
   - Meta Developer account

2. **Create Meta App**
   - Go to developers.facebook.com
   - Create new App → Select "Business" type
   - Add "Instagram Graph API" product

3. **Configure Instagram Graph API**
   - Add Instagram Basic Display product (if needed)
   - Configure OAuth redirect URIs:
     - Development: `https://influence-me.test/auth/instagram/callback`
     - Production: `https://yourdomain.com/auth/instagram/callback`

4. **Required Permissions**
   - `instagram_basic` — read profile info and media
   - `instagram_manage_insights` — read insights/analytics
   - `pages_show_list` — list Facebook Pages
   - `pages_read_engagement` — read Page engagement data
   - `business_management` — manage business assets
   - Explain what each permission grants

5. **Environment Variables**
   ```
   INSTAGRAM_CLIENT_ID=your_app_id
   INSTAGRAM_CLIENT_SECRET=your_app_secret
   INSTAGRAM_REDIRECT_URI=https://influence-me.test/auth/instagram/callback
   ```

6. **Token Flow**
   - Short-lived token (1 hour) → Exchange for long-lived token (60 days)
   - Token refresh before expiry

7. **App Review (Production)**
   - Which permissions need review
   - What to include in review submission
   - Timeline expectations

8. **Troubleshooting**
   - Common errors and solutions
   - Rate limiting (200 calls/user/hour)

## Files to Create
- `docs/meta-app-setup.md`

## Also Update
- `.env.example` — add `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, `INSTAGRAM_REDIRECT_URI`

## Acceptance Criteria
- [ ] Guide is clear enough for someone unfamiliar with Meta APIs
- [ ] All required permissions listed with explanations
- [ ] Environment variables documented
- [ ] Both development and production setup covered
- [ ] `.env.example` updated
