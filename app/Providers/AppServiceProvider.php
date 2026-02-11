<?php

declare(strict_types=1);

namespace App\Providers;

use App\Clients\Facebook\Contracts\FacebookOAuthClientInterface;
use App\Clients\Facebook\Data\FacebookAppCredentials;
use App\Clients\Facebook\FacebookOAuthClient;
use App\Connectors\Facebook\Contracts\FacebookGraphConnectorInterface;
use App\Connectors\Facebook\FacebookGraphConnector;
use Carbon\CarbonImmutable;
use FacebookAds\AnonymousSession;
use FacebookAds\Api;
use FacebookAds\Http\Client as FacebookHttpClient;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FacebookAppCredentials::class, function (): FacebookAppCredentials {
            return new FacebookAppCredentials(
                clientId: (string) config('services.facebook.client_id', ''),
                clientSecret: (string) config('services.facebook.client_secret', ''),
            );
        });

        $this->app->singleton(FacebookGraphConnectorInterface::class, function (): FacebookGraphConnectorInterface {
            $api = new Api(new FacebookHttpClient, new AnonymousSession);
            $api->setDefaultGraphVersion('24.0');

            return new FacebookGraphConnector($api);
        });

        $this->app->bind(FacebookOAuthClientInterface::class, FacebookOAuthClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
