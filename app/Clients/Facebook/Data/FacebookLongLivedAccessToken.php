<?php

namespace App\Clients\Facebook\Data;

final readonly class FacebookLongLivedAccessToken
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
    ) {}
}
