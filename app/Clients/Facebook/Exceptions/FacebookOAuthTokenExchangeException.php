<?php

declare(strict_types=1);

namespace App\Clients\Facebook\Exceptions;

use RuntimeException;

/**
 * Raised when Facebook rejects OAuth token exchange or payload is invalid.
 */
final class FacebookOAuthTokenExchangeException extends RuntimeException {}
