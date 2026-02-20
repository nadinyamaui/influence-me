<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class StripeException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?int $invoiceId = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
