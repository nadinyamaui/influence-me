<?php

namespace App\Jobs;

use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordFollowerSnapshot implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public InstagramAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        FollowerSnapshot::query()->create([
            'instagram_account_id' => $this->account->id,
            'followers_count' => max((int) $this->account->followers_count, 0),
            'recorded_at' => now(),
        ]);
    }
}
