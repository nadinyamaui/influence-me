<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class TikTokApiException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?int $accountId = null,
        public readonly ?string $endpoint = null,
        public readonly ?string $apiErrorCode = null,
        public readonly bool $rateLimited = false,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
