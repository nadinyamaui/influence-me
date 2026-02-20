<?php

use App\Exceptions\InstagramApiException;
use App\Exceptions\StripeException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'client.auth' => \App\Http\Middleware\AuthenticateClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->report(function (InstagramApiException $exception): void {
            Log::channel('instagram')->error($exception->getMessage(), [
                'account_id' => $exception->accountId,
                'endpoint' => $exception->endpoint,
            ]);
        });

        $exceptions->report(function (StripeException $exception): void {
            Log::channel('stripe')->error($exception->getMessage(), [
                'invoice_id' => $exception->invoiceId,
            ]);
        });
    })->create();
