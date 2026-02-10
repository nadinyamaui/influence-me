<?php

declare(strict_types=1);

namespace App\Clients\Facebook\Exceptions;

use RuntimeException;

/**
 * Raised when Facebook OAuth configuration is missing or invalid.
 */
final class FacebookOAuthMisconfigurationException extends RuntimeException {}
