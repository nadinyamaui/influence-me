<?php

use App\Jobs\RefreshInstagramToken;
use App\Jobs\SyncAllInstagramData;
use App\Jobs\SyncInstagramProfile;
use App\Jobs\SyncMediaInsights;
use App\Models\InstagramAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;

function findScheduledEvent(string $name): ?\Illuminate\Console\Scheduling\Event
{
    return collect(app(Schedule::class)->events())
        ->first(fn (\Illuminate\Console\Scheduling\Event $event): bool => $event->description === $name);
}

it('registers instagram scheduler events with expected frequencies', function (): void {
    $fullSyncEvent = findScheduledEvent('sync-all-instagram');
    $refreshEvent = findScheduledEvent('refresh-instagram-insights');
    $tokenRefreshEvent = findScheduledEvent('refresh-instagram-tokens');

    expect($fullSyncEvent)->not->toBeNull()
        ->and($refreshEvent)->not->toBeNull()
        ->and($tokenRefreshEvent)->not->toBeNull()
        ->and($fullSyncEvent->expression)->toBe('0 */6 * * *')
        ->and($refreshEvent->expression)->toBe('0 * * * *')
        ->and($tokenRefreshEvent->expression)->toBe('0 0 * * *');
});

it('dispatches full sync orchestrator jobs for each instagram account', function (): void {
    InstagramAccount::factory()->count(3)->create();
    Bus::fake();

    findScheduledEvent('sync-all-instagram')->run(app());

    Bus::assertDispatched(SyncAllInstagramData::class, 3);
});

it('dispatches profile and insights refresh jobs for each instagram account', function (): void {
    InstagramAccount::factory()->count(2)->create();
    Bus::fake();

    findScheduledEvent('refresh-instagram-insights')->run(app());

    Bus::assertDispatched(SyncInstagramProfile::class, 2);
    Bus::assertDispatched(SyncMediaInsights::class, 2);
});

it('dispatches token refresh only for accounts expiring within seven days', function (): void {
    InstagramAccount::factory()->create(['token_expires_at' => now()->addDays(3)]);
    InstagramAccount::factory()->create(['token_expires_at' => now()->addDays(8)]);
    Bus::fake();

    findScheduledEvent('refresh-instagram-tokens')->run(app());

    Bus::assertDispatched(RefreshInstagramToken::class, 1);
});
