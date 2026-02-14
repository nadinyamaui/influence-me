<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Models\InstagramAccount;
use App\Services\Facebook\InstagramGraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInstagramProfile implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(public InstagramAccount $account, public bool $finalizeSyncState = true)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        try {
            $profile = app(InstagramGraphService::class, ['account' => $this->account])->getProfile();
        } catch (InstagramTokenExpiredException $exception) {
            Log::warning('Instagram profile sync failed due to expired token.', [
                'instagram_account_id' => $this->account->id,
                'message' => $exception->getMessage(),
            ]);

            $this->account->update([
                'sync_status' => SyncStatus::Failed,
                'last_sync_error' => $exception->getMessage(),
            ]);

            return;
        } catch (InstagramApiException $exception) {
            throw $exception;
        }

        $updates = [
            'username' => $profile['username'],
            'name' => $profile['name'],
            'biography' => $profile['biography'],
            'profile_picture_url' => $profile['profile_picture_url'],
            'followers_count' => $profile['followers_count'],
            'following_count' => $profile['following_count'],
            'media_count' => $profile['media_count'],
        ];

        if ($this->finalizeSyncState) {
            $updates['sync_status'] = SyncStatus::Idle;
            $updates['last_synced_at'] = now();
            $updates['last_sync_error'] = null;
        }

        $this->account->update($updates);
    }
}
