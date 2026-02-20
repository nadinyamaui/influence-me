<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Models\SocialAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshSocialMediaToken implements ShouldQueue
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
        if ($this->account->token_expires_at?->isPast()) {
            $message = 'Instagram token has expired and requires account re-authentication.';

            $this->account->update([
                'sync_status' => SyncStatus::Failed,
                'last_sync_error' => $message,
            ]);

            Log::warning('Instagram token refresh skipped because token already expired.', [
                'social_account_id' => $this->account->id,
            ]);

            return;
        }

        try {
            $newToken = $this->account->refreshLongLivedToken();
        } catch (InstagramTokenExpiredException $exception) {
            $message = 'Instagram token refresh failed because token is expired and requires account re-authentication.';

            $this->account->update([
                'sync_status' => SyncStatus::Failed,
                'last_sync_error' => $message,
            ]);

            Log::warning('Instagram token refresh failed due to expired token.', [
                'social_account_id' => $this->account->id,
                'message' => $exception->getMessage(),
            ]);

            return;
        } catch (InstagramApiException $exception) {
            $this->account->update([
                'last_sync_error' => $exception->getMessage(),
            ]);

            Log::error('Instagram token refresh failed due to API error.', [
                'social_account_id' => $this->account->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->account->update([
            'access_token' => $newToken,
            'token_expires_at' => now()->addDays(60),
            'sync_status' => SyncStatus::Idle,
            'last_sync_error' => null,
        ]);

        Log::info('Instagram token refreshed successfully.', [
            'social_account_id' => $this->account->id,
        ]);
    }
}
