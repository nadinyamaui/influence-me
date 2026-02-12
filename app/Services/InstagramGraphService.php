<?php

namespace App\Services;

use App\Models\InstagramAccount;
use App\Services\Facebook\Client as FacebookClient;

class InstagramGraphService
{
    public function __construct(
        protected InstagramAccount $account
    ) {}

    public function getMedia(?string $after = null): array
    {
        return (new FacebookClient($this->account->access_token))->getMedia($after);
    }
}
