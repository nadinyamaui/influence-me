<?php

namespace App\Services\Tiktok;

use App\Exceptions\TikTokApiException;
use App\Exceptions\TikTokTokenExpiredException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class TikTokApiConnector
{
    protected PendingRequest $client;

    public function __construct(
        protected ?string $accessToken = null,
        protected ?int $accountId = null,
    ) {
        $this->client = Http::baseUrl($this->baseUrl())
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

        if ($response->failed() || $this->hasApiError($payload)) {
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
        $message = $this->extractErrorMessage($payload);
        $apiErrorCode = $this->extractErrorCode($payload);
        $rateLimited = $this->isRateLimited(statusCode: $statusCode, message: $message);

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

    protected function hasApiError(array $payload): bool
    {
        $error = $payload['error'] ?? null;
        if (is_array($error)) {
            $errorCode = $error['code'] ?? $error['error_code'] ?? null;

            if ($errorCode === 0 || $errorCode === '0') {
                return false;
            }

            if ($errorCode !== null && $errorCode !== '') {
                return true;
            }

            $errorMessage = $error['message'] ?? $error['description'] ?? $error['msg'] ?? null;
            if (is_string($errorMessage) && $errorMessage !== '') {
                return true;
            }
        }

        $code = $payload['code'] ?? $payload['error_code'] ?? null;
        if (is_numeric($code)) {
            return (int) $code !== 0;
        }

        return false;
    }

    protected function extractErrorMessage(array $payload): string
    {
        $error = $payload['error'] ?? null;
        if (is_array($error)) {
            $message = $error['message']
                ?? $error['description']
                ?? $error['msg']
                ?? null;

            if (is_string($message) && $message !== '') {
                return $message;
            }
        }

        $message = $payload['message'] ?? $payload['msg'] ?? null;

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return 'TikTok API request failed.';
    }

    protected function extractErrorCode(array $payload): ?string
    {
        $error = $payload['error'] ?? null;
        $code = $error['code'] ?? $error['error_code'] ?? $payload['code'] ?? $payload['error_code'] ?? null;

        if ($code === null || $code === '') {
            return null;
        }

        return (string) $code;
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

    protected function isRateLimited(int $statusCode, string $message): bool
    {
        if ($statusCode === 429) {
            return true;
        }

        $normalizedMessage = strtolower($message);

        return str_contains($normalizedMessage, 'rate limit')
            || str_contains($normalizedMessage, 'too many requests');
    }

    protected function baseUrl(): string
    {
        return (string) config('services.tiktok.base_url', 'https://open.tiktokapis.com');
    }

    protected function timeoutSeconds(): int
    {
        $timeout = (int) config('services.tiktok.timeout', 10);

        return $timeout > 0 ? $timeout : 10;
    }

    protected function retryTimes(): int
    {
        $retryTimes = (int) config('services.tiktok.retry_times', 3);

        return $retryTimes >= 0 ? $retryTimes : 3;
    }

    protected function retrySleepMilliseconds(): int
    {
        $retrySleepMs = (int) config('services.tiktok.retry_sleep_ms', 200);

        return $retrySleepMs >= 0 ? $retrySleepMs : 200;
    }
}
