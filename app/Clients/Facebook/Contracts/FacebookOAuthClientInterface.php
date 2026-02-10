<?php

declare(strict_types=1);

namespace App\Clients\Facebook\Contracts;

use App\Clients\Facebook\Data\FacebookLongLivedAccessToken;
use App\Clients\Facebook\Exceptions\FacebookOAuthMisconfigurationException;
use App\Clients\Facebook\Exceptions\FacebookOAuthTokenExchangeException;

/**
 * Contract for Facebook OAuth token operations.
 */
interface FacebookOAuthClientInterface
{
    /**
     * Exchanges a short-lived token for a long-lived token.
     *
     * @throws FacebookOAuthMisconfigurationException
     * @throws FacebookOAuthTokenExchangeException
     */
    public function exchangeForLongLivedAccessToken(string $shortLivedAccessToken): FacebookLongLivedAccessToken;
}
