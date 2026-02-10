<?php

namespace App\Connectors\Facebook;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FacebookGraphConnector
{
    private const BASE_URL = 'https://graph.facebook.com/v23.0';

    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(self::BASE_URL)->acceptJson();
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): Response
    {
        return $this->http->get('/'.ltrim($path, '/'), $query);
    }
}
