<?php

namespace App\Connectors\Facebook;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FacebookGraphConnector
{
    private const BASE_URL = 'https://graph.facebook.com/v23.0';

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): Response
    {
        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->get('/'.ltrim($path, '/'), $query);
    }
}

