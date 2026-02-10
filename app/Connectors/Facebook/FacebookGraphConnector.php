<?php

namespace App\Connectors\Facebook;

use FacebookAds\AnonymousSession;
use FacebookAds\Api;
use FacebookAds\Http\Client as FacebookHttpClient;
use FacebookAds\Http\Exception\RequestException;
use FacebookAds\Http\RequestInterface;

class FacebookGraphConnector
{
    private Api $api;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        ?Api $api = null,
    ) {
        $this->api = $api ?? new Api(
            new FacebookHttpClient,
            new AnonymousSession
        );
        $this->api->setDefaultGraphVersion('v24.0');
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->api->call(
                $endpoint,
                RequestInterface::METHOD_GET,
                array_merge(
                    [
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ],
                    $query,
                ),
            );
        } catch (RequestException $exception) {
            $payload = $exception->getResponse()?->getContent();

            return is_array($payload)
                ? $payload
                : [];
        }

        $payload = $response->getContent();

        return is_array($payload)
            ? $payload
            : [];
    }
}
