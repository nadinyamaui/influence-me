<?php

namespace App\Jobs;

use App\Models\SocialAccount;
use App\Services\Facebook\InstagramGraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAudienceDemographics implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public SocialAccount $account)
    {
        $this->onQueue('instagram-sync');
    }

    public function handle(): void
    {
        app(InstagramGraphService::class, ['account' => $this->account])->syncAudienceDemographics();
    }
}
