<?php

use Illuminate\Console\Scheduling\Schedule;

it('registers RFC 027 instagram scheduler tasks with expected cadence', function (): void {
    $events = collect(app(Schedule::class)->events());

    $fullSyncEvent = $events->first(fn ($event) => $event->description === 'sync-all-instagram');
    $refreshEvent = $events->first(fn ($event) => $event->description === 'refresh-instagram-insights');
    $tokenRefreshEvent = $events->first(fn ($event) => $event->description === 'refresh-instagram-tokens');

    expect($fullSyncEvent)->not->toBeNull()
        ->and($fullSyncEvent->expression)->toBe('0 */6 * * *')
        ->and($refreshEvent)->not->toBeNull()
        ->and($refreshEvent->expression)->toBe('0 * * * *')
        ->and($tokenRefreshEvent)->not->toBeNull()
        ->and($tokenRefreshEvent->expression)->toBe('0 0 * * *');
});
