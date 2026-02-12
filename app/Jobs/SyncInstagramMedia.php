<?php

namespace App\Jobs;

use App\Models\InstagramAccount;
use App\Services\Facebook\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncInstagramMedia implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public InstagramAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        app(MediaService::class, ['account' => $this->account])->retrieveMedia();
    }
}
