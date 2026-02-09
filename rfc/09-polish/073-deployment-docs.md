# 073 - Deployment Documentation

**Labels:** `documentation`
**Depends on:** All previous issues

## Description

Create comprehensive deployment documentation covering all environment configuration, server requirements, and operational procedures.

## Document to Create: `docs/deployment.md`

### Sections

**1. Server Requirements**
- PHP 8.4+ with extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml
- MySQL 8.0+ or PostgreSQL 15+
- Redis (for queues, cache, Horizon)
- Node.js 20+ (for building frontend assets)
- Supervisor (for queue workers)

**2. Environment Variables**
Complete list of all env vars with descriptions:
```
# Application
APP_NAME, APP_ENV, APP_KEY, APP_URL

# Database
DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Redis
REDIS_HOST, REDIS_PASSWORD, REDIS_PORT

# Instagram OAuth
INSTAGRAM_CLIENT_ID, INSTAGRAM_CLIENT_SECRET, INSTAGRAM_REDIRECT_URI

# Stripe
STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET

# Mail
MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS

# Queue
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=redis
```

**3. Deployment Steps**
1. Clone repository
2. `composer install --no-dev`
3. `npm install && npm run build`
4. Copy `.env.example` to `.env` and configure
5. `php artisan key:generate`
6. `php artisan migrate`
7. `php artisan db:seed` (optional, for demo data)
8. Configure web server (Nginx/Apache)
9. Set up SSL (required for OAuth callbacks)

**4. Queue Workers (Supervisor)**
```ini
[program:influence-me-horizon]
process_name=%(program_name)s
command=php /path/to/artisan horizon
autostart=true
autorestart=true
```

**5. Cron (Scheduled Tasks)**
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks:
- Full Instagram sync: every 6 hours
- Profile/insights refresh: hourly
- Token refresh: daily
- Overdue invoice detection: daily at 9 AM
- Follower snapshots: daily

**6. Meta App Review**
Checklist for submitting Instagram API for production access:
- Screencast of the app
- Privacy policy URL
- Terms of service URL
- App verification

**7. Stripe Webhook Configuration**
- Set up webhook in Stripe Dashboard pointing to `https://yourdomain.com/webhooks/stripe`
- Select event: `checkout.session.completed`
- Copy webhook secret to `STRIPE_WEBHOOK_SECRET`

## Also Update
- `.env.example` — ensure ALL variables documented with comments

## Files to Create
- `docs/deployment.md`

## Files to Modify
- `.env.example` — add comments for all variables

## Acceptance Criteria
- [ ] All environment variables documented
- [ ] Server requirements listed
- [ ] Step-by-step deployment guide
- [ ] Queue worker configuration documented
- [ ] Cron schedule documented
- [ ] Meta App Review checklist included
- [ ] Stripe webhook setup documented
