<?php

use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('creates valid client user records with hashed passwords', function (): void {
    $clientUser = ClientUser::factory()->create([
        'password' => 'secret-pass',
    ]);

    expect($clientUser->client)->toBeInstanceOf(Client::class)
        ->and($clientUser->password)->not->toBe('secret-pass')
        ->and(Hash::check('secret-pass', $clientUser->password))->toBeTrue();
});

it('defines client relationship', function (): void {
    $clientUser = ClientUser::factory()->create();

    expect($clientUser->client())->toBeInstanceOf(BelongsTo::class)
        ->and($clientUser->client)->toBeInstanceOf(Client::class);
});

it('can be authenticated by a dedicated client guard configuration', function (): void {
    $clientUser = ClientUser::factory()->create();

    Auth::guard('client')->login($clientUser);

    expect($clientUser)->toBeInstanceOf(AuthenticatableContract::class)
        ->and(Auth::guard('client')->check())->toBeTrue()
        ->and(Auth::guard('client')->id())->toBe($clientUser->id)
        ->and(Auth::guard('client')->user()?->is($clientUser))->toBeTrue();
});
