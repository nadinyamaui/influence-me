<?php

namespace App\Connectors\Facebook;

use FacebookAds\AnonymousSession;
use FacebookAds\Api;
use FacebookAds\Http\Client as FacebookHttpClient;
use FacebookAds\Http\Exception\RequestException;
use FacebookAds\Http\RequestInterface;

class FacebookGraphConnector
{
    /**
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        $api = new Api(
            new FacebookHttpClient,
            new AnonymousSession
        );
        $api->setDefaultGraphVersion('v23.0');

        try {
            $response = $api->call(
                $endpoint,
                RequestInterface::METHOD_GET,
                $query,
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
