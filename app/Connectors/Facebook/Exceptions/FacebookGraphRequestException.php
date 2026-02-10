<?php

declare(strict_types=1);

namespace App\Connectors\Facebook\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Raised when the Facebook Graph request transport fails.
 */
final class FacebookGraphRequestException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        string $message,
        private readonly array $payload = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
