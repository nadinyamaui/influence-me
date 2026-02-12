<?php

namespace App\Services;

use App\Models\InstagramAccount;
use App\Services\Facebook\Client as FacebookClient;

class InstagramGraphService
{
    public function __construct(
        protected ?InstagramAccount $account = null
    ) {}

    public function forAccount(InstagramAccount $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getMedia(?string $after = null): array
    {
        if (! $this->account instanceof InstagramAccount) {
            throw new \RuntimeException('Instagram account must be configured before making API calls.');
        }

        return (new FacebookClient($this->account->access_token))->getMedia($after);
    }
}
