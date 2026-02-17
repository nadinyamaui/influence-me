<?php

use App\Jobs\RecordFollowerSnapshot;
use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use Illuminate\Support\Carbon;

it('records a follower snapshot for the instagram account', function (): void {
    $frozenNow = Carbon::parse('2026-02-17 12:00:00');
    Carbon::setTestNow($frozenNow);

    $account = InstagramAccount::factory()->create([
        'followers_count' => 12345,
    ]);

    try {
        app(RecordFollowerSnapshot::class, ['account' => $account])->handle();
        $snapshot = FollowerSnapshot::query()->where('instagram_account_id', $account->id)->first();

        expect(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->count())->toBe(1)
            ->and(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->value('followers_count'))->toBe(12345)
            ->and($snapshot)->not->toBeNull()
            ->and($snapshot?->recorded_at?->toDateString())->toBe($frozenNow->toDateString());
    } finally {
        Carbon::setTestNow();
    }
});

it('updates the existing account snapshot when rerun for the same day', function (): void {
    $frozenNow = Carbon::parse('2026-02-17 12:00:00');
    Carbon::setTestNow($frozenNow);

    $account = InstagramAccount::factory()->create([
        'followers_count' => 1000,
    ]);

    try {
        app(RecordFollowerSnapshot::class, ['account' => $account])->handle();

        $account->update(['followers_count' => 1500]);

        app(RecordFollowerSnapshot::class, ['account' => $account])->handle();

        expect(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->count())->toBe(1)
            ->and(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->value('followers_count'))->toBe(1500);
    } finally {
        Carbon::setTestNow();
    }
});

it('configures queue and retry backoff settings', function (): void {
    $account = InstagramAccount::factory()->create();

    $job = new RecordFollowerSnapshot($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([60, 300, 900]);
});
