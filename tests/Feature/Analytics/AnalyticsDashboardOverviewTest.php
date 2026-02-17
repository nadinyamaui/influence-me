<?php

use App\Enums\MediaType;
use App\Livewire\Analytics\Index;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from analytics page', function (): void {
    $this->get(route('analytics.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view analytics page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertSuccessful()
        ->assertSee('Analytics')
        ->assertSee('Total Followers')
        ->assertSee('Total Reach')
        ->assertSee('Audience Growth Chart')
        ->assertSee('href="'.route('analytics.index').'"', false);
});

test('analytics overview cards calculate metrics and filters in query layer', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $primaryAccount = InstagramAccount::factory()->for($user)->create([
        'followers_count' => 150000,
        'username' => 'primary',
    ]);

    $secondaryAccount = InstagramAccount::factory()->for($user)->create([
        'followers_count' => 50000,
        'username' => 'secondary',
    ]);

    $outsiderAccount = InstagramAccount::factory()->for($otherUser)->create([
        'followers_count' => 999999,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'media_type' => MediaType::Post,
        'published_at' => now()->subDays(2),
        'engagement_rate' => 4.50,
        'reach' => 1200,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => now()->subDays(10),
        'engagement_rate' => 6.50,
        'reach' => 2400,
    ]);

    InstagramMedia::factory()->for($secondaryAccount)->create([
        'media_type' => MediaType::Story,
        'published_at' => now()->subDays(20),
        'engagement_rate' => 2.00,
        'reach' => 400,
    ]);

    InstagramMedia::factory()->for($secondaryAccount)->create([
        'media_type' => MediaType::Post,
        'published_at' => now()->subDays(45),
        'engagement_rate' => 9.00,
        'reach' => 10000,
    ]);

    InstagramMedia::factory()->for($outsiderAccount)->create([
        'media_type' => MediaType::Post,
        'published_at' => now()->subDay(),
        'engagement_rate' => 99.00,
        'reach' => 900000,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('200,000')
        ->assertSee('3')
        ->assertSee('1 posts')
        ->assertSee('1 reels')
        ->assertSee('1 stories')
        ->assertSee('4.33%')
        ->assertSee('4.0K')
        ->set('period', '7_days')
        ->assertSee('1 posts')
        ->assertSee('0 reels')
        ->assertSee('0 stories')
        ->assertSee('4.50%')
        ->assertSee('1.2K')
        ->set('period', '90_days')
        ->set('accountId', (string) $secondaryAccount->id)
        ->assertSee('50,000')
        ->assertSee('2')
        ->assertSee('1 posts')
        ->assertSee('0 reels')
        ->assertSee('1 stories')
        ->assertSee('5.50%')
        ->assertSee('10.4K')
        ->assertDontSee('999,999');
});
