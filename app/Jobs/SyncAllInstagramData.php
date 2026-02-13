<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\InstagramAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Throwable;

class SyncAllInstagramData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public InstagramAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $accountId = $this->account->id;

        $this->account->update([
            'sync_status' => SyncStatus::Syncing,
            'last_sync_error' => null,
        ]);

        Bus::chain([
            new SyncInstagramProfile($this->account),
            new SyncInstagramMedia($this->account),
            new SyncMediaInsights($this->account),
            new SyncInstagramStories($this->account),
            new SyncAudienceDemographics($this->account),
            new MarkInstagramSyncComplete($this->account),
        ])
            ->onQueue('instagram-sync')
            ->catch(function (Throwable $exception) use ($accountId): void {
                InstagramAccount::query()
                    ->whereKey($accountId)
                    ->update([
                        'sync_status' => SyncStatus::Failed,
                        'last_sync_error' => $exception->getMessage(),
                    ]);
            })
            ->dispatch();
    }
}
