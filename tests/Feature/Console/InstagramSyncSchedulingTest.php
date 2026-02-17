<?php

use App\Jobs\RecordFollowerSnapshot;
use App\Jobs\RefreshInstagramToken;
use App\Models\InstagramAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

it('registers RFC 027 instagram scheduler tasks with expected cadence', function (): void {
    $events = collect(app(Schedule::class)->events());

    $fullSyncEvent = $events->first(fn ($event) => $event->description === 'sync-all-instagram');
    $refreshEvent = $events->first(fn ($event) => $event->description === 'refresh-instagram-insights');
    $tokenRefreshEvent = $events->first(fn ($event) => $event->description === 'refresh-instagram-tokens');
    $followerSnapshotEvent = $events->first(fn ($event) => $event->description === 'record-follower-snapshots');

    expect($fullSyncEvent)->not->toBeNull()
        ->and($fullSyncEvent->expression)->toBe('0 */6 * * *')
        ->and($refreshEvent)->not->toBeNull()
        ->and($refreshEvent->expression)->toBe('0 * * * *')
        ->and($tokenRefreshEvent)->not->toBeNull()
        ->and($tokenRefreshEvent->expression)->toBe('0 0 * * *')
        ->and($followerSnapshotEvent)->not->toBeNull()
        ->and($followerSnapshotEvent->expression)->toBe('0 0 * * *');
});

it('dispatches token refresh only for accounts expiring within the next seven days', function (): void {
    Bus::fake();

    $expiredAccount = InstagramAccount::factory()->create([
        'token_expires_at' => now()->subDay(),
    ]);
    $expiringSoonAccount = InstagramAccount::factory()->create([
        'token_expires_at' => now()->addDays(3),
    ]);
    $laterAccount = InstagramAccount::factory()->create([
        'token_expires_at' => now()->addDays(12),
    ]);

    $events = collect(app(Schedule::class)->events());
    $tokenRefreshEvent = $events->first(fn ($event) => $event->description === 'refresh-instagram-tokens');

    expect($tokenRefreshEvent)->not->toBeNull();

    $tokenRefreshEvent->run(app());

    Bus::assertDispatched(RefreshInstagramToken::class, function (RefreshInstagramToken $job) use ($expiringSoonAccount): bool {
        return $job->account->is($expiringSoonAccount);
    });

    Bus::assertNotDispatched(RefreshInstagramToken::class, function (RefreshInstagramToken $job) use ($expiredAccount): bool {
        return $job->account->is($expiredAccount);
    });

    Bus::assertNotDispatched(RefreshInstagramToken::class, function (RefreshInstagramToken $job) use ($laterAccount): bool {
        return $job->account->is($laterAccount);
    });
});

it('dispatches follower snapshots for each instagram account', function (): void {
    Bus::fake();
    Carbon::setTestNow('2026-02-17 00:05:00');

    $firstAccount = InstagramAccount::factory()->create();
    $secondAccount = InstagramAccount::factory()->create();

    $events = collect(app(Schedule::class)->events());
    $followerSnapshotEvent = $events->first(fn ($event) => $event->description === 'record-follower-snapshots');

    expect($followerSnapshotEvent)->not->toBeNull();

    $followerSnapshotEvent->run(app());

    Bus::assertDispatched(RecordFollowerSnapshot::class, function (RecordFollowerSnapshot $job) use ($firstAccount): bool {
        return $job->account->is($firstAccount);
    });

    Bus::assertDispatched(RecordFollowerSnapshot::class, function (RecordFollowerSnapshot $job) use ($secondAccount): bool {
        return $job->account->is($secondAccount);
    });

    Carbon::setTestNow();
});
