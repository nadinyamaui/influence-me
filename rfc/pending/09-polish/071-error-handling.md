# 071 - Error Handling and Monitoring

**Labels:** `enhancement`, `infrastructure`
**Depends on:** #020

## Description

Set up comprehensive error handling for external API integrations and configure the existing monitoring tools (Telescope, Horizon, Pulse).

## Custom Exceptions
Already created in earlier issues:
- `App\Exceptions\InstagramApiException`
- `App\Exceptions\InstagramTokenExpiredException`

## Error Pages

### Create Custom Error Views
- `resources/views/errors/403.blade.php` — "You don't have permission to access this page"
- `resources/views/errors/404.blade.php` — "Page not found"
- `resources/views/errors/500.blade.php` — "Something went wrong"
- `resources/views/errors/503.blade.php` — "We'll be right back"

Style consistently with the app using Flux UI.

## Exception Handler
In `bootstrap/app.php`:
```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->report(function (InstagramApiException $e) {
        Log::channel('instagram')->error($e->getMessage(), [
            'account_id' => $e->accountId,
            'endpoint' => $e->endpoint,
        ]);
    });

})
```

## Logging Channels
Add to `config/logging.php`:
```php
'instagram' => [
    'driver' => 'daily',
    'path' => storage_path('logs/instagram.log'),
    'days' => 14,
],
```

## Horizon Configuration
Verify `config/horizon.php` has:
- `instagram-sync` queue with appropriate worker count
- Proper retry and timeout settings
- Email notification on long wait times

## Pulse Configuration
Add relevant Pulse cards to dashboard for monitoring:
- Queue throughput
- Slow queries
- Exceptions

## Files to Create
- `resources/views/errors/403.blade.php`
- `resources/views/errors/404.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/views/errors/503.blade.php`

## Files to Modify
- `bootstrap/app.php` — exception reporting
- `config/logging.php` — add channels
- `config/horizon.php` — verify queue config

## Acceptance Criteria
- [ ] Custom error pages render with consistent styling
- [ ] Instagram API errors logged to dedicated channel
- [ ] Horizon configured for instagram-sync queue
- [ ] Pulse dashboard shows relevant metrics
