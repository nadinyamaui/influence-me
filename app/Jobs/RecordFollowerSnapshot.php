<?php

namespace App\Jobs;

use App\Models\FollowerSnapshot;
use App\Models\SocialAccount;
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

    public function __construct(public SocialAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $recordedAt = now()->toDateString();
        $followersCount = max((int) $this->account->followers_count, 0);

        $snapshot = FollowerSnapshot::query()
            ->where('social_account_id', $this->account->id)
            ->whereDate('recorded_at', $recordedAt)
            ->first();

        if ($snapshot !== null) {
            $snapshot->update([
                'followers_count' => $followersCount,
            ]);

            return;
        }

        FollowerSnapshot::query()->create([
            'social_account_id' => $this->account->id,
            'recorded_at' => $recordedAt,
            'followers_count' => $followersCount,
        ]);
    }
}
