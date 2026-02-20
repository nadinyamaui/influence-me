<?php

use App\Jobs\SyncInstagramMedia;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Bus;

it('runs media retrieval immediately for all instagram accounts by default', function (): void {
    SocialAccount::factory()->count(3)->create();
    Bus::fake();

    $this->artisan('instagram:media:retrieve-all')
        ->expectsOutput('Retrieved Instagram media for 3 account(s).')
        ->assertExitCode(0);

    Bus::assertDispatchedSync(SyncInstagramMedia::class, 3);
});

it('queues media retrieval jobs for all instagram accounts when queue option is used', function (): void {
    SocialAccount::factory()->count(2)->create();
    Bus::fake();

    $this->artisan('instagram:media:retrieve-all --queue')
        ->expectsOutput('Queued Instagram media sync for 2 account(s).')
        ->assertExitCode(0);

    Bus::assertDispatched(SyncInstagramMedia::class, 2);
    Bus::assertNotDispatchedSync(SyncInstagramMedia::class);
});

it('reports when there are no instagram accounts to sync', function (): void {
    Bus::fake();

    $this->artisan('instagram:media:retrieve-all')
        ->expectsOutput('No Instagram accounts found.')
        ->assertExitCode(0);

    Bus::assertNothingDispatched();
});
