<?php

use App\Enums\ScheduledPostStatus;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('creates valid scheduled post records with factory defaults and casts', function (): void {
    $scheduledPost = ScheduledPost::factory()->create();

    expect($scheduledPost->user)->toBeInstanceOf(User::class)
        ->and($scheduledPost->client)->toBeInstanceOf(Client::class)
        ->and($scheduledPost->instagramAccount)->toBeInstanceOf(InstagramAccount::class)
        ->and($scheduledPost->client?->user_id)->toBe($scheduledPost->user_id)
        ->and($scheduledPost->instagramAccount->user_id)->toBe($scheduledPost->user_id)
        ->and($scheduledPost->status)->toBe(ScheduledPostStatus::Planned)
        ->and($scheduledPost->scheduled_at)->not->toBeNull()
        ->and($scheduledPost->scheduled_at->isFuture())->toBeTrue();
});

it('supports planned published and cancelled factory states', function (): void {
    $planned = ScheduledPost::factory()->planned()->create();
    $published = ScheduledPost::factory()->published()->create();
    $cancelled = ScheduledPost::factory()->cancelled()->create();

    expect($planned->status)->toBe(ScheduledPostStatus::Planned)
        ->and($planned->scheduled_at->isFuture())->toBeTrue()
        ->and($published->status)->toBe(ScheduledPostStatus::Published)
        ->and($published->scheduled_at->isPast())->toBeTrue()
        ->and($cancelled->status)->toBe(ScheduledPostStatus::Cancelled);
});

it('defines user client and instagram account relationships with typed returns', function (): void {
    $scheduledPost = ScheduledPost::factory()->create();

    expect($scheduledPost->user())->toBeInstanceOf(BelongsTo::class)
        ->and($scheduledPost->client())->toBeInstanceOf(BelongsTo::class)
        ->and($scheduledPost->instagramAccount())->toBeInstanceOf(BelongsTo::class);
});

it('allows scheduled posts without a client', function (): void {
    $scheduledPost = ScheduledPost::factory()->create([
        'client_id' => null,
    ]);

    expect($scheduledPost->client_id)->toBeNull()
        ->and($scheduledPost->client)->toBeNull();
});

it('defines user scheduled posts relationship', function (): void {
    $user = User::factory()->create();
    $instagramAccount = InstagramAccount::factory()->for($user)->create();

    ScheduledPost::factory()->for($user)->for($instagramAccount)->create();
    ScheduledPost::factory()->for($user)->for($instagramAccount)->create();

    expect($user->scheduledPosts())->toBeInstanceOf(HasMany::class)
        ->and($user->scheduledPosts)->toHaveCount(2);
});
