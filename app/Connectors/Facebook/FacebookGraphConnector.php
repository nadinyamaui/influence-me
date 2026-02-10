<?php

declare(strict_types=1);

namespace App\Connectors\Facebook;

use App\Connectors\Facebook\Contracts\FacebookGraphConnectorInterface;
use App\Connectors\Facebook\Exceptions\FacebookGraphRequestException;
use FacebookAds\Api;
use FacebookAds\Http\Exception\RequestException;

/**
 * Connector for generic Facebook Graph API requests using FacebookAds SDK.
 */
final class FacebookGraphConnector implements FacebookGraphConnectorInterface
{
    public function __construct(
        private readonly Api $api,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws FacebookGraphRequestException
     */
    public function request(string $path, string $method = 'GET', array $params = []): array
    {
        try {
            $response = $this->api->call(
                $path,
                strtoupper($method),
                $params,
            );
        } catch (RequestException $exception) {
            $payload = $exception->getResponse()?->getContent();
            $payload = is_array($payload) ? $payload : [];

            throw new FacebookGraphRequestException(
                message: 'Facebook Graph request failed.',
                payload: $payload,
                previous: $exception,
            );
        }

        $payload = $response->getContent();

        return is_array($payload) ? $payload : [];
    }
}
