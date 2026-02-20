# Deployment Guide

This document covers production deployment requirements and operating procedures for Influence Me.

## RFC Reference

- RFC `073` - Deployment Documentation

## 1. Server Requirements

- PHP `8.4+`
- Required PHP extensions:
  - `bcmath`
  - `ctype`
  - `curl`
  - `dom`
  - `fileinfo`
  - `json`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `tokenizer`
  - `xml`
- Database:
  - MySQL `8.0+` or
  - PostgreSQL `15+`
- Redis (queues, cache, sessions, Horizon)
- Node.js `20+` (frontend asset build)
- Supervisor (process management for Horizon)
- Web server (Nginx or Apache) with HTTPS

## 2. Environment Variables

Configure these values in each deployed environment.

### Application

- `APP_NAME`: App display name
- `APP_ENV`: Environment (`production`, `staging`, etc.)
- `APP_KEY`: Application encryption key
- `APP_URL`: Public app URL

### Database

- `DB_CONNECTION`: Database driver (`mysql` or `pgsql`)
- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database user
- `DB_PASSWORD`: Database password

### Redis

- `REDIS_HOST`: Redis host
- `REDIS_PASSWORD`: Redis password (or `null`)
- `REDIS_PORT`: Redis port

### Instagram OAuth

- `INSTAGRAM_CLIENT_ID`: Meta App client ID
- `INSTAGRAM_CLIENT_SECRET`: Meta App client secret
- `INSTAGRAM_REDIRECT_URI`: OAuth callback URL

### Stripe

- `STRIPE_KEY`: Stripe publishable key
- `STRIPE_SECRET`: Stripe secret key
- `STRIPE_WEBHOOK_SECRET`: Stripe webhook signing secret

### Mail

- `MAIL_MAILER`: Mail transport (`smtp`, etc.)
- `MAIL_HOST`: SMTP host
- `MAIL_PORT`: SMTP port
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password
- `MAIL_FROM_ADDRESS`: Sender email

### Queue

- `QUEUE_CONNECTION=redis`

### Session

- `SESSION_DRIVER=redis`

## 3. Deployment Steps

1. Clone repository.
2. Install PHP dependencies:
   - `composer install --no-dev --optimize-autoloader`
3. Install Node dependencies and build assets:
   - `npm install`
   - `npm run build`
4. Copy environment file and configure:
   - `cp .env.example .env`
   - fill all required secrets/URLs
5. Generate app key:
   - `php artisan key:generate`
6. Run database migrations:
   - `php artisan migrate --force`
7. Seed data when needed (optional demo environments):
   - `php artisan db:seed --force`
8. Configure web server root to `/public`.
9. Set up SSL/TLS certificates (required for OAuth callbacks).
10. Cache production config/views:
    - `php artisan config:cache`
    - `php artisan view:cache`

## 4. Queue Workers (Supervisor)

Use Horizon under Supervisor for queue processing.

```ini
[program:influence-me-horizon]
process_name=%(program_name)s
command=php /path/to/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/influence-me-horizon.log
stopwaitsecs=3600
```

Then reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start influence-me-horizon
```

## 5. Cron (Scheduled Tasks)

Add cron entry:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Current scheduled workloads:

- Full Instagram sync: every 6 hours
- Profile + insights refresh: hourly
- Token refresh: daily
- Follower snapshots: daily

## 6. Meta App Review

Before production Instagram access, prepare:

- App screencast showing login + data usage
- Public privacy policy URL
- Public terms of service URL
- Completed app verification in Meta Developer Dashboard

## 7. Stripe Webhook Configuration

1. In Stripe Dashboard, create a webhook endpoint:
   - `https://yourdomain.com/webhooks/stripe`
2. Subscribe to event:
   - `checkout.session.completed`
3. Copy signing secret and set:
   - `STRIPE_WEBHOOK_SECRET`
4. Verify Laravel route is reachable and CSRF-exempt for webhooks.

## Operational Notes

- Keep `APP_DEBUG=false` in production.
- Ensure queue workers and cron are monitored.
- Run `php artisan route:cache` only after removing all closure routes.
- Rotate OAuth and Stripe secrets using your secrets manager.
