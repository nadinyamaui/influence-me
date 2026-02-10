<?php

namespace App\Clients\Facebook;

use App\Clients\Facebook\Data\FacebookLongLivedAccessToken;
use App\Clients\Facebook\Exceptions\FacebookOAuthTokenExchangeException;
use App\Connectors\Facebook\FacebookGraphConnector;

class FacebookOAuthClient
{
    public function __construct(
        private readonly FacebookGraphConnector $connector,
    ) {}

    public function exchangeForLongLivedAccessToken(string $shortLivedAccessToken): FacebookLongLivedAccessToken
    {
        $clientId = (string) config('services.facebook.client_id', '');
        $clientSecret = (string) config('services.facebook.client_secret', '');

        if ($clientId === '' || $clientSecret === '') {
            throw new FacebookOAuthTokenExchangeException('Facebook OAuth credentials are not configured.');
        }

        $payload = $this->connector->get('/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $shortLivedAccessToken,
        ]);

        $errorCode = (int) ($payload['error']['code'] ?? 0);
        if ($errorCode > 0) {
            throw new FacebookOAuthTokenExchangeException('Facebook OAuth token exchange failed.');
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
}
