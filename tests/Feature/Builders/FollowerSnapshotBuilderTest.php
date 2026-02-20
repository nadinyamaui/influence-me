<?php

use App\Enums\AnalyticsPeriod;
use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use App\Models\User;
use Carbon\CarbonImmutable;

it('scopes snapshots to user account and analytics period and orders by date', function (): void {
    CarbonImmutable::setTestNow('2026-02-20 10:00:00');

    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerAccount = InstagramAccount::factory()->for($owner)->create();
    $ownerOtherAccount = InstagramAccount::factory()->for($owner)->create();
    $outsiderAccount = InstagramAccount::factory()->for($outsider)->create();

    FollowerSnapshot::factory()->for($ownerAccount)->create([
        'recorded_at' => '2026-02-18',
        'followers_count' => 1200,
    ]);
    FollowerSnapshot::factory()->for($ownerAccount)->create([
        'recorded_at' => '2026-02-14',
        'followers_count' => 1100,
    ]);
    FollowerSnapshot::factory()->for($ownerOtherAccount)->create([
        'recorded_at' => '2026-02-19',
        'followers_count' => 800,
    ]);
    FollowerSnapshot::factory()->for($outsiderAccount)->create([
        'recorded_at' => '2026-02-18',
        'followers_count' => 5000,
    ]);

    $dates = FollowerSnapshot::query()
        ->forUser($owner->id)
        ->filterByAccount((string) $ownerAccount->id)
        ->forAnalyticsPeriod(AnalyticsPeriod::SevenDays)
        ->orderedByRecordedAt()
        ->get()
        ->map(fn (FollowerSnapshot $snapshot): string => $snapshot->recorded_at->toDateString())
        ->all();

    expect($dates)->toBe(['2026-02-14', '2026-02-18']);

    CarbonImmutable::setTestNow();
});

it('keeps all user accounts when account filter is all and analytics period is all time', function (): void {
    CarbonImmutable::setTestNow('2026-02-20 10:00:00');

    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerFirstAccount = InstagramAccount::factory()->for($owner)->create();
    $ownerSecondAccount = InstagramAccount::factory()->for($owner)->create();
    $outsiderAccount = InstagramAccount::factory()->for($outsider)->create();

    FollowerSnapshot::factory()->for($ownerFirstAccount)->create(['recorded_at' => '2025-12-01']);
    FollowerSnapshot::factory()->for($ownerSecondAccount)->create(['recorded_at' => '2026-02-19']);
    FollowerSnapshot::factory()->for($outsiderAccount)->create(['recorded_at' => '2026-02-19']);

    $count = FollowerSnapshot::query()
        ->forUser($owner->id)
        ->filterByAccount('all')
        ->forAnalyticsPeriod(AnalyticsPeriod::AllTime)
        ->count();

    expect($count)->toBe(2);

    CarbonImmutable::setTestNow();
});
