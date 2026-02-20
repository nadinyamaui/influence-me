<?php

namespace App\Jobs;

use App\Models\SocialAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMediaInsights implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(public SocialAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        $this->account->syncMediaInsights();
    }
}
