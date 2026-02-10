<?php

declare(strict_types=1);

namespace App\Clients\Facebook\Data;

/**
 * Credentials required for Facebook OAuth app-level operations.
 */
final readonly class FacebookAppCredentials
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
    ) {}
}
