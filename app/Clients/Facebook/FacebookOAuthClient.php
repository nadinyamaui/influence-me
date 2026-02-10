<?php

declare(strict_types=1);

namespace App\Clients\Facebook;

use App\Clients\Facebook\Contracts\FacebookOAuthClientInterface;
use App\Clients\Facebook\Data\FacebookAppCredentials;
use App\Clients\Facebook\Data\FacebookLongLivedAccessToken;
use App\Clients\Facebook\Exceptions\FacebookOAuthMisconfigurationException;
use App\Clients\Facebook\Exceptions\FacebookOAuthTokenExchangeException;
use App\Connectors\Facebook\Contracts\FacebookGraphConnectorInterface;
use App\Connectors\Facebook\Exceptions\FacebookGraphRequestException;

/**
 * Facebook OAuth use-case client that exchanges short-lived user tokens.
 */
final class FacebookOAuthClient implements FacebookOAuthClientInterface
{
    public function __construct(
        private readonly FacebookGraphConnectorInterface $connector,
        private readonly FacebookAppCredentials $credentials,
    ) {
        if ($this->credentials->clientId === '' || $this->credentials->clientSecret === '') {
            throw new FacebookOAuthMisconfigurationException('Facebook OAuth credentials are not configured.');
        }
    }

    /**
     * Exchanges a short-lived Facebook user token for a long-lived token.
     *
     * @throws FacebookOAuthMisconfigurationException
     * @throws FacebookOAuthTokenExchangeException
     */
    public function exchangeForLongLivedAccessToken(string $shortLivedAccessToken): FacebookLongLivedAccessToken
    {
        if ($shortLivedAccessToken === '') {
            throw new FacebookOAuthTokenExchangeException('Short-lived Facebook token is required.');
        }

        try {
            $payload = $this->connector->request('/oauth/access_token', 'GET', [
                'client_id' => $this->credentials->clientId,
                'client_secret' => $this->credentials->clientSecret,
                'grant_type' => 'fb_exchange_token',
                'fb_exchange_token' => $shortLivedAccessToken,
            ]);
        } catch (FacebookGraphRequestException $exception) {
            throw new FacebookOAuthTokenExchangeException(
                message: $this->buildFacebookErrorMessage($exception->payload(), 'Facebook OAuth token exchange request failed.'),
                previous: $exception,
            );
        }

        if (is_array($payload['error'] ?? null)) {
            throw new FacebookOAuthTokenExchangeException(
                $this->buildFacebookErrorMessage($payload, 'Facebook OAuth token exchange failed.')
            );
        }

        $accessToken = (string) ($payload['access_token'] ?? '');
        $expiresIn = (int) ($payload['expires_in'] ?? 0);
        if ($accessToken === '' || $expiresIn <= 0) {
            throw new FacebookOAuthTokenExchangeException('Facebook OAuth token exchange returned an incomplete payload.');
        }

        return new FacebookLongLivedAccessToken(
            accessToken: $accessToken,
            expiresIn: $expiresIn,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function buildFacebookErrorMessage(array $payload, string $fallback): string
    {
        $error = $payload['error'] ?? null;
        if (! is_array($error)) {
            return $fallback;
        }

        $message = (string) ($error['message'] ?? $fallback);
        $type = (string) ($error['type'] ?? '');
        $code = (string) ($error['code'] ?? '');
        $subcode = (string) ($error['error_subcode'] ?? '');
        $parts = array_filter([
            $message,
            $type !== '' ? "type={$type}" : null,
            $code !== '' ? "code={$code}" : null,
            $subcode !== '' ? "subcode={$subcode}" : null,
        ]);

        return implode(' | ', $parts);
    }
}
