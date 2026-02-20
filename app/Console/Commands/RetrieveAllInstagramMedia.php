<?php

namespace App\Console\Commands;

use App\Jobs\SyncSocialMediaMedia;
use App\Models\SocialAccount;
use Illuminate\Console\Command;

class RetrieveAllInstagramMedia extends Command
{
    protected $signature = 'instagram:media:retrieve-all {--queue : Queue sync jobs instead of running immediately}';

    protected $description = 'Retrieve media for all Instagram accounts';

    public function handle(): int
    {
        $totalAccounts = 0;

        SocialAccount::query()
            ->orderBy('id')
            ->cursor()
            ->each(function (SocialAccount $account) use (&$totalAccounts): void {
                $totalAccounts++;

                if ($this->option('queue')) {
                    SyncSocialMediaMedia::dispatch($account);

                    return;
                }

                SyncSocialMediaMedia::dispatchSync($account);
            });

        if ($totalAccounts === 0) {
            $this->warn('No Instagram accounts found.');

            return self::SUCCESS;
        }

        $message = $this->option('queue')
            ? 'Queued Instagram media sync for'
            : 'Retrieved Instagram media for';

        $this->info(sprintf('%s %d account(s).', $message, $totalAccounts));

        return self::SUCCESS;
    }
}
