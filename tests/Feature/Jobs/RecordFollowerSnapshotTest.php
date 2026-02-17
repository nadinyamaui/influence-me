<?php

use App\Jobs\RecordFollowerSnapshot;
use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;

it('records a follower snapshot for the instagram account', function (): void {
    $account = InstagramAccount::factory()->create([
        'followers_count' => 12345,
    ]);

    app(RecordFollowerSnapshot::class, ['account' => $account])->handle();

    expect(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->count())->toBe(1)
        ->and(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->value('followers_count'))->toBe(12345)
        ->and(FollowerSnapshot::query()->where('instagram_account_id', $account->id)->value('recorded_at'))->not->toBeNull();
});

it('configures queue and retry backoff settings', function (): void {
    $account = InstagramAccount::factory()->create();

    $job = new RecordFollowerSnapshot($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([60, 300, 900]);
});
