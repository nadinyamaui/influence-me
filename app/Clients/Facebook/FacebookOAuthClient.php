<?php

namespace App\Clients\Facebook;

use App\Clients\Facebook\Data\FacebookLongLivedAccessToken;
use App\Clients\Facebook\Exceptions\FacebookOAuthTokenExchangeException;
use App\Connectors\Facebook\FacebookGraphConnector;

class FacebookOAuthClient
{
    private FacebookGraphConnector $connector;

    private string $clientId;

    private string $clientSecret;

    public function __construct()
    {
        $this->clientId = (string) config('services.facebook.client_id', '');
        $this->clientSecret = (string) config('services.facebook.client_secret', '');

        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new FacebookOAuthTokenExchangeException('Facebook OAuth credentials are not configured.');
        }

        $this->connector = new FacebookGraphConnector(
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
        );
    }

    public function exchangeForLongLivedAccessToken(string $shortLivedAccessToken): FacebookLongLivedAccessToken
    {
        $payload = $this->connector->get('/oauth/access_token', [
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
