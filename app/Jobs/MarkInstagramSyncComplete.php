<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\InstagramAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkInstagramSyncComplete implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public InstagramAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $this->account->update([
            'sync_status' => SyncStatus::Idle,
            'last_synced_at' => now(),
            'last_sync_error' => null,
        ]);
    }
}
