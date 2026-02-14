<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\InstagramAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Throwable;

class SyncAllInstagramData implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public InstagramAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $this->account->update([
            'sync_status' => SyncStatus::Syncing,
            'last_sync_error' => null,
        ]);

        $accountId = $this->account->id;

        try {
            Bus::chain([
                new SyncInstagramProfile($this->account, finalizeSyncState: false),
                new SyncInstagramMedia($this->account),
                new SyncMediaInsights($this->account),
                new SyncInstagramStories($this->account),
                new SyncAudienceDemographics($this->account),
                function () use ($accountId): void {
                    InstagramAccount::query()
                        ->whereKey($accountId)
                        ->where('sync_status', '!=', SyncStatus::Failed->value)
                        ->update([
                            'sync_status' => SyncStatus::Idle,
                            'last_synced_at' => now(),
                            'last_sync_error' => null,
                        ]);
                },
            ])->onQueue('instagram-sync')
                ->catch(function (Throwable $exception) use ($accountId): void {
                    InstagramAccount::query()->whereKey($accountId)->update([
                        'sync_status' => SyncStatus::Failed,
                        'last_sync_error' => $exception->getMessage(),
                    ]);
                })
                ->dispatch();
        } catch (Throwable $exception) {
            InstagramAccount::query()->whereKey($accountId)->update([
                'sync_status' => SyncStatus::Failed,
                'last_sync_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
