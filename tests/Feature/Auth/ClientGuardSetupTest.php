<?php

use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('registers a dedicated client guard provider and password broker', function (): void {
    expect(config('auth.guards.client'))->toBe([
        'driver' => 'session',
        'provider' => 'clients',
    ]);

    expect(config('auth.providers.clients'))->toBe([
        'driver' => 'eloquent',
        'model' => ClientUser::class,
    ]);

    expect(config('auth.passwords.clients'))->toBe([
        'provider' => 'clients',
        'table' => config('auth.passwords.users.table'),
        'expire' => 60,
        'throttle' => 60,
    ]);
});

it('keeps influencer web guard configuration unchanged', function (): void {
    expect(config('auth.guards.web'))->toBe([
        'driver' => 'session',
        'provider' => 'users',
    ]);

    expect(config('auth.providers.users.driver'))->toBe('eloquent')
        ->and(config('auth.providers.users.model'))->toBe(User::class);
});

it('allows client users to authenticate via the client guard', function (): void {
    $clientUser = ClientUser::factory()->create();

    Auth::guard('client')->login($clientUser);

    expect(Auth::guard('client')->check())->toBeTrue()
        ->and(Auth::guard('client')->id())->toBe($clientUser->id)
        ->and(Auth::guard('client')->user()?->is($clientUser))->toBeTrue();
});

it('loads the dedicated portal routes file from web routes', function (): void {
    $webRoutes = file_get_contents(base_path('routes/web.php'));

    expect($webRoutes)->toContain("require __DIR__.'/portal.php';")
        ->and(file_exists(base_path('routes/portal.php')))->toBeTrue();
});
