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
   - Meta/Facebook user account with access to the connected Page
   - Meta Developer account

2. **Create Meta App**
   - Go to developers.facebook.com
   - Create new App → Select "Business" type
   - Add "Instagram Graph API" product

3. **Configure Instagram Graph API**
   - Add Facebook Login for Business (or Facebook Login)
   - Configure OAuth redirect URIs:
     - Development: `https://influence-me.test/auth/facebook/callback`
     - Production: `https://yourdomain.com/auth/facebook/callback`

4. **Required Permissions**
   - `instagram_basic` — read profile info and media
   - `instagram_manage_insights` — read insights/analytics
   - `pages_show_list` — list Facebook Pages
   - `pages_read_engagement` — read Page engagement data
   - `business_management` — manage business assets
   - Explain what each permission grants

5. **Environment Variables**
   ```
   META_CLIENT_ID=your_app_id
   META_CLIENT_SECRET=your_app_secret
   META_REDIRECT_URI=https://influence-me.test/auth/facebook/callback
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
   - Explicit note: users without Meta/Facebook accounts cannot complete Instagram Graph OAuth
   - Rate limiting (200 calls/user/hour)

## Files to Create
- `docs/meta-app-setup.md`

## Also Update
- `.env.example` — add `META_CLIENT_ID`, `META_CLIENT_SECRET`, `META_REDIRECT_URI`

## Acceptance Criteria
- [ ] Guide is clear enough for someone unfamiliar with Meta APIs
- [ ] All required permissions listed with explanations
- [ ] Environment variables documented
- [ ] Both development and production setup covered
- [ ] `.env.example` updated
