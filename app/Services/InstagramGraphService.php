<?php

namespace App\Services;

use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Models\InstagramAccount;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class InstagramGraphService
{
    public function __construct(
        protected InstagramAccount $account
    ) {}

    public function getMedia(?string $after = null): array
    {
        $query = [
            'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'access_token' => $this->account->access_token,
        ];

        if ($after !== null) {
            $query['after'] = $after;
        }

        try {
            $response = Http::baseUrl('https://graph.instagram.com/v21.0')
                ->get('/me/media', $query)
                ->throw();
        } catch (RequestException $exception) {
            $errorCode = $exception->response->json('error.code');

            if ((int) $errorCode === 190) {
                throw new InstagramTokenExpiredException('Instagram access token is expired.', 190, $exception);
            }

            throw new InstagramApiException('Instagram media request failed.', (int) ($errorCode ?? 0), $exception);
        }

        return $response->json();
    }
}
