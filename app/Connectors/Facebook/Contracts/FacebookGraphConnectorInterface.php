<?php

declare(strict_types=1);

namespace App\Connectors\Facebook\Contracts;

use App\Connectors\Facebook\Exceptions\FacebookGraphRequestException;

/**
 * Generic contract for Facebook Graph API requests.
 */
interface FacebookGraphConnectorInterface
{
    /**
     * Sends a request to the Facebook Graph API.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws FacebookGraphRequestException
     */
    public function request(string $path, string $method = 'GET', array $params = []): array;
}
