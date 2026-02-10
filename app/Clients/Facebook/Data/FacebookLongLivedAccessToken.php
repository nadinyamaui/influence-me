<?php

declare(strict_types=1);

namespace App\Clients\Facebook\Data;

/**
 * Long-lived token payload returned from Facebook OAuth exchange.
 */
final readonly class FacebookLongLivedAccessToken
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
    ) {}
}
