<?php

use App\Enums\MediaType;
use App\Livewire\Content\Index;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from content gallery page', function (): void {
    $this->get(route('content.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view scoped content gallery page', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = InstagramAccount::factory()->for($user)->create([
        'username' => 'owneraccount',
    ]);

    $otherAccount = InstagramAccount::factory()->for($otherUser)->create();

    InstagramMedia::factory()->for($account)->create([
        'caption' => 'Owner gallery post',
    ]);

    InstagramMedia::factory()->for($otherAccount)->create([
        'caption' => 'Hidden outsider post',
    ]);

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertSuccessful()
        ->assertSee('Content')
        ->assertSee('Owner gallery post')
        ->assertDontSee('Hidden outsider post')
        ->assertSee('href="'.route('content.index').'"', false);
});

test('content gallery filters and sorting options work in query layer', function (): void {
    $user = User::factory()->create();

    $primaryAccount = InstagramAccount::factory()->for($user)->create();
    $secondaryAccount = InstagramAccount::factory()->for($user)->create();

    InstagramMedia::factory()->for($primaryAccount)->create([
        'caption' => 'Primary Post Item',
        'media_type' => MediaType::Post,
        'published_at' => now()->subDay(),
        'like_count' => 100,
        'reach' => 300,
        'engagement_rate' => 3.20,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'caption' => 'Primary Reel Item',
        'media_type' => MediaType::Reel,
        'published_at' => now()->subDays(2),
        'like_count' => 500,
        'reach' => 120,
        'engagement_rate' => 8.10,
    ]);

    InstagramMedia::factory()->for($secondaryAccount)->create([
        'caption' => 'Secondary Story Item',
        'media_type' => MediaType::Story,
        'published_at' => now()->subDays(3),
        'like_count' => 250,
        'reach' => 900,
        'engagement_rate' => 5.55,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('mediaType', MediaType::Reel->value)
        ->assertSee('Primary Reel Item')
        ->assertDontSee('Primary Post Item')
        ->assertDontSee('Secondary Story Item')
        ->set('mediaType', 'all')
        ->set('accountId', (string) $secondaryAccount->id)
        ->assertSee('Secondary Story Item')
        ->assertDontSee('Primary Post Item')
        ->set('accountId', 'all')
        ->set('dateFrom', now()->subDays(2)->format('Y-m-d'))
        ->assertSee('Primary Post Item')
        ->assertSee('Primary Reel Item')
        ->assertDontSee('Secondary Story Item')
        ->set('dateFrom', null)
        ->set('sortBy', 'most_liked')
        ->assertSeeInOrder([
            'Primary Reel Item',
            'Secondary Story Item',
            'Primary Post Item',
        ])
        ->set('sortBy', 'highest_reach')
        ->assertSeeInOrder([
            'Secondary Story Item',
            'Primary Post Item',
            'Primary Reel Item',
        ])
        ->set('sortBy', 'best_engagement')
        ->assertSeeInOrder([
            'Primary Reel Item',
            'Secondary Story Item',
            'Primary Post Item',
        ]);
});

test('content gallery uses cursor pagination', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::factory()->for($user)->create();

    foreach (range(1, 25) as $number) {
        InstagramMedia::factory()->for($account)->create([
            'caption' => 'Paged Item '.$number,
            'published_at' => now()->subMinutes($number),
        ]);
    }

    $response = $this->actingAs($user)->get(route('content.index'));

    $response->assertSuccessful()
        ->assertSee('Paged Item 1')
        ->assertSee('Paged Item 24')
        ->assertDontSee('Paged Item 25');

    $nextCursor = InstagramMedia::query()
        ->where('instagram_account_id', $account->id)
        ->orderBy('published_at', 'desc')
        ->orderByDesc('id')
        ->cursorPaginate(24, ['*'], 'cursor')
        ->nextCursor()?->encode();

    expect($nextCursor)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('content.index', ['cursor' => $nextCursor]))
        ->assertSuccessful()
        ->assertSee('Paged Item 25')
        ->assertDontSee('Paged Item 1');
});

test('content gallery shows an empty state when no media exists', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertSuccessful()
        ->assertSee('No content synced yet. Connect an Instagram account and run a sync.');
});
