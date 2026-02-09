<?php

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

it('creates valid client records with factory defaults and casts', function (): void {
    $client = Client::factory()->create();

    expect($client->user)->toBeInstanceOf(User::class)
        ->and($client->name)->not->toBeEmpty()
        ->and($client->type)->toBeInstanceOf(ClientType::class);
});

it('supports brand and individual factory states', function (): void {
    $brand = Client::factory()->brand()->create();
    $individual = Client::factory()->individual()->create();

    expect($brand->type)->toBe(ClientType::Brand)
        ->and($brand->company_name)->not->toBeNull()
        ->and($individual->type)->toBe(ClientType::Individual)
        ->and($individual->company_name)->toBeNull();
});

it('defines user client user proposals invoices and media relationships', function (): void {
    $client = Client::factory()->create();
    $media = InstagramMedia::factory()->create();

    $client->instagramMedia()->attach($media->id, [
        'campaign_name' => 'Spring Campaign',
        'notes' => 'Feature placement',
    ]);

    $proposalsReturnType = (new ReflectionMethod(Client::class, 'proposals'))
        ->getReturnType()?->getName();
    $invoicesReturnType = (new ReflectionMethod(Client::class, 'invoices'))
        ->getReturnType()?->getName();

    expect($client->user())->toBeInstanceOf(BelongsTo::class)
        ->and($client->clientUser())->toBeInstanceOf(HasOne::class)
        ->and($proposalsReturnType)->toBe(HasMany::class)
        ->and($invoicesReturnType)->toBe(HasMany::class)
        ->and($client->instagramMedia())->toBeInstanceOf(BelongsToMany::class)
        ->and($client->instagramMedia)->toHaveCount(1)
        ->and($client->instagramMedia->first()->id)->toBe($media->id)
        ->and($client->instagramMedia->first()->pivot->campaign_name)->toBe('Spring Campaign');
});

it('defines user clients relationship', function (): void {
    $user = User::factory()->create();

    Client::factory()->for($user)->create();
    Client::factory()->for($user)->create();

    expect($user->clients())->toBeInstanceOf(HasMany::class)
        ->and($user->clients)->toHaveCount(2);
});
