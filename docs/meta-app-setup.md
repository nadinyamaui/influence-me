# Meta App Setup for Instagram Graph API

This guide walks through creating and configuring a Meta app for Influence Me Instagram OAuth and API sync.

## RFC Reference
- RFC `014` - Meta App Setup Documentation

## Prerequisites
- Instagram account is `Business` or `Creator` (not Personal)
- Instagram account is connected to a Facebook Page
- User signing in has a Meta/Facebook account with access to that Page
- Meta Developer account at `https://developers.facebook.com/`
- Local app URL available for callback testing (example: `https://influence-me.test`)

## 1. Create a Meta App
1. Sign in to [Meta for Developers](https://developers.facebook.com/).
2. Open `My Apps` and click `Create App`.
3. Choose `Business` as the app type.
4. Enter app name, app contact email, and business account (if prompted).
5. Create the app and complete verification prompts.

## 2. Add Products
1. In the app dashboard, add the `Instagram Graph API` product.
2. Add `Facebook Login for Business` (or `Facebook Login` if your account only shows that option).
3. Add `Webhooks` only if you plan to support webhook events later.

## 3. Configure OAuth Redirect URIs
Set redirect URIs in the Facebook Login settings.

### Development
- `https://influence-me.test/auth/facebook/callback`

### Production
- `https://yourdomain.com/auth/facebook/callback`

Notes:
- URI must exactly match your Laravel route and environment value.
- Keep HTTPS enabled in production.
- Add both local and production URLs before testing each environment.

## 4. Confirm Instagram + Page Linkage
Instagram Graph API requires an Instagram professional account tied to a Facebook Page.

1. In Instagram app: `Settings -> Account -> Switch to professional account` if needed.
2. In Meta Business/Page settings: connect the Instagram account to the Page.
3. Confirm the same Page is accessible by the Meta user that will authorize the app.

## 5. Required Permissions
Request and configure the following scopes.

- `instagram_basic`: read Instagram account profile and media metadata
- `instagram_manage_insights`: read account and media insights used for analytics
- `pages_show_list`: list Pages the user can access during account linking
- `pages_read_engagement`: read Page engagement-related data required by linked Instagram flows
- `business_management`: access business assets needed in business-managed account setups

For development mode, only app roles (admin/developer/tester) can grant scopes. For production use, permissions requiring review must be approved by Meta.

## 6. Environment Variables
Add the following keys to environment files:

```env
META_CLIENT_ID=your_app_id
META_CLIENT_SECRET=your_app_secret
META_REDIRECT_URI=https://influence-me.test/auth/facebook/callback
```

Where to use them:
- `.env` for local development
- server secrets manager or production env config for deployed environments

## 7. Token Flow
Influence Me should use the standard Instagram Graph token lifecycle.

1. User completes OAuth and receives a short-lived user token (about 1 hour).
2. Backend exchanges it for a long-lived token (about 60 days).
3. App stores token expiry timestamp.
4. Scheduled refresh runs before expiry to avoid failed sync jobs.

Operational guidance:
- Refresh daily for tokens expiring soon.
- If refresh fails, mark account sync status as failed and surface reconnect actions.

## 8. App Review for Production
Before external users can authorize your app with protected scopes:

1. Move the app from Development to Live mode.
2. Submit required permissions for App Review.
3. Include complete screencast + written steps that show:
   - login flow
   - why each permission is needed
   - where data appears in product UI
4. Provide test credentials if requested.

Typical review timeline: 3-10 business days, depending on submission quality and policy queue.

## 9. Troubleshooting

### Invalid redirect URI
- Ensure URI in Meta app exactly equals `META_REDIRECT_URI` in environment config.
- Check scheme (`https`), host, path, and trailing slash mismatches.

### Missing permissions or access denied
- Verify the user has app role in Development mode.
- Confirm app is Live and permission approved for non-role users.

### No Pages available
- Confirm user has Facebook Page access and the Page is linked to the Instagram professional account.

### User has no Facebook account
- Instagram Graph API OAuth depends on Meta/Facebook identity and Page permissions.
- Create or use a Meta/Facebook account, grant it Page access, then reconnect.

### Token expired / sync failures
- Re-authenticate if long-lived token cannot be refreshed.
- Confirm scheduled token refresh is running.

### Rate limiting
- Plan for API rate limits (commonly cited baseline is about 200 calls per user per hour, subject to Meta policy and endpoint context).
- Use retries with backoff, cache stable reads, and avoid unnecessary polling.

## 10. Go-Live Checklist
- App type is `Business`
- Instagram Graph API product enabled
- Redirect URIs configured for both environments
- Required permissions configured and reviewed (for production)
- Environment variables set in local and production
- Token refresh scheduler enabled
- At least one end-to-end OAuth test completed
