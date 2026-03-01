<?php

namespace App\Services\SocialMedia\Tiktok;

use App\Exceptions\TikTokApiException;
use App\Exceptions\TikTokTokenExpiredException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class Connector
{
    private const BASE_URL = 'https://open.tiktokapis.com';
    private const TIMEOUT_SECONDS = 10;
    private const RETRY_TIMES = 3;
    private const RETRY_SLEEP_MILLISECONDS = 200;

    protected PendingRequest $client;

    public function __construct(
        protected ?string $accessToken = null,
        protected ?int $accountId = null,
    ) {
        $this->client = Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->timeout($this->timeoutSeconds())
            ->retry($this->retryTimes(), $this->retrySleepMilliseconds(), throw: false)
            ->withToken($this->accessToken ?? '');
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    public function request(string $method, string $endpoint, array $options = []): array
    {
        $normalizedEndpoint = '/'.ltrim($endpoint, '/');

        try {
            $response = $this->client->send($method, $normalizedEndpoint, $options);
        } catch (ConnectionException $exception) {
            throw new TikTokApiException(
                message: 'TikTok API connection failed.',
                previous: $exception,
                accountId: $this->accountId,
                endpoint: $normalizedEndpoint,
            );
        } catch (RequestException $exception) {
            $response = $exception->response;

            if ($response instanceof Response) {
                $payload = $response->json();
                if (! is_array($payload)) {
                    $payload = ['raw' => $response->body()];
                }

                throw $this->mapApiException(
                    endpoint: $normalizedEndpoint,
                    statusCode: $response->status(),
                    payload: $payload,
                );
            }

            throw new TikTokApiException(
                message: 'TikTok API request failed.',
                previous: $exception,
                accountId: $this->accountId,
                endpoint: $normalizedEndpoint,
            );
        } catch (Throwable $exception) {
            throw new TikTokApiException(
                message: 'TikTok API request failed.',
                previous: $exception,
                accountId: $this->accountId,
                endpoint: $normalizedEndpoint,
            );
        }

        return $this->normalizeResponse($response, $normalizedEndpoint);
    }

    protected function normalizeResponse(Response $response, string $endpoint): array
    {
        $payload = $response->json();
        if (! is_array($payload)) {
            $payload = ['raw' => $response->body()];
        }

        if ($response->failed()) {
            throw $this->mapApiException(
                endpoint: $endpoint,
                statusCode: $response->status(),
                payload: $payload,
            );
        }

        $data = $payload['data'] ?? $payload;

        return is_array($data) ? $data : ['data' => $data];
    }

    protected function mapApiException(string $endpoint, int $statusCode, array $payload): TikTokApiException
    {
        $message = $payload['error']['message'] ?? 'TikTok API Request Failed';
        $apiErrorCode = $payload['error']['code'] ?? '1';
        $rateLimited = $this->isRateLimited(statusCode: $statusCode);

        if ($this->isTokenExpired(statusCode: $statusCode, message: $message, apiErrorCode: $apiErrorCode)) {
            return new TikTokTokenExpiredException(
                message: $message,
                code: $statusCode,
                accountId: $this->accountId,
                endpoint: $endpoint,
                apiErrorCode: $apiErrorCode,
                rateLimited: $rateLimited,
            );
        }

        return new TikTokApiException(
            message: $message,
            code: $statusCode,
            accountId: $this->accountId,
            endpoint: $endpoint,
            apiErrorCode: $apiErrorCode,
            rateLimited: $rateLimited,
        );
    }

    protected function isTokenExpired(int $statusCode, string $message, ?string $apiErrorCode): bool
    {
        if ($statusCode === 401) {
            return true;
        }

        $normalizedMessage = strtolower($message);
        if (str_contains($normalizedMessage, 'token') && (str_contains($normalizedMessage, 'expire') || str_contains($normalizedMessage, 'invalid'))) {
            return true;
        }

        return in_array($apiErrorCode, ['access_token_invalid', 'access_token_expired', '40100', '40101'], true);
    }

    protected function isRateLimited(int $statusCode): bool
    {
        return $statusCode === 429;
    }

    protected function timeoutSeconds(): int
    {
        return self::TIMEOUT_SECONDS;
    }

    protected function retryTimes(): int
    {
        return self::RETRY_TIMES;
    }

    protected function retrySleepMilliseconds(): int
    {
        return self::RETRY_SLEEP_MILLISECONDS;
    }
}
