<?php

use App\Enums\MediaType;
use App\Livewire\Analytics\Index;
use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use Carbon\CarbonImmutable;
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

    FollowerSnapshot::factory()->for($primaryAccount)->create([
        'followers_count' => 150000,
        'recorded_at' => now()->subDays(20),
    ]);

    FollowerSnapshot::factory()->for($secondaryAccount)->create([
        'followers_count' => 50000,
        'recorded_at' => now()->subDays(20),
    ]);

    FollowerSnapshot::factory()->for($primaryAccount)->create([
        'followers_count' => 151200,
        'recorded_at' => now()->subDays(5),
    ]);

    FollowerSnapshot::factory()->for($secondaryAccount)->create([
        'followers_count' => 50200,
        'recorded_at' => now()->subDays(5),
    ]);

    FollowerSnapshot::factory()->for($outsiderAccount)->create([
        'followers_count' => 999999,
        'recorded_at' => now()->subDays(5),
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
        ->assertViewHas('chart', fn (array $chart): bool => $chart['labels'] === [
            now()->subDays(20)->toDateString(),
            now()->subDays(5)->toDateString(),
        ] && $chart['data'] === [200000, 201400])
        ->set('period', '7_days')
        ->assertSee('1 posts')
        ->assertSee('0 reels')
        ->assertSee('0 stories')
        ->assertSee('4.50%')
        ->assertSee('1.2K')
        ->assertViewHas('chart', fn (array $chart): bool => $chart['labels'] === [now()->subDays(5)->toDateString()] && $chart['data'] === [201400])
        ->set('period', '90_days')
        ->set('accountId', (string) $secondaryAccount->id)
        ->assertSee('50,000')
        ->assertSee('2')
        ->assertSee('1 posts')
        ->assertSee('0 reels')
        ->assertSee('1 stories')
        ->assertSee('5.50%')
        ->assertSee('10.4K')
        ->assertViewHas('chart', fn (array $chart): bool => $chart['labels'] === [
            now()->subDays(20)->toDateString(),
            now()->subDays(5)->toDateString(),
        ] && $chart['data'] === [50000, 50200])
        ->assertDontSee('999,999');
});

test('engagement trend chart data aggregates by period granularity and account filter', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-17 12:00:00'));

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $primaryAccount = InstagramAccount::factory()->for($user)->create([
        'followers_count' => 120000,
        'username' => 'trend-main',
    ]);

    $secondaryAccount = InstagramAccount::factory()->for($user)->create([
        'followers_count' => 35000,
        'username' => 'trend-alt',
    ]);

    $outsiderAccount = InstagramAccount::factory()->for($otherUser)->create([
        'followers_count' => 888888,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2026-02-15 10:00:00',
        'engagement_rate' => 4.00,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2026-02-15 18:00:00',
        'engagement_rate' => 8.00,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2026-01-30 15:00:00',
        'engagement_rate' => 10.00,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2026-01-28 09:00:00',
        'engagement_rate' => 14.00,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2025-12-05 09:00:00',
        'engagement_rate' => 20.00,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'published_at' => '2025-12-26 09:00:00',
        'engagement_rate' => 22.00,
    ]);

    InstagramMedia::factory()->for($secondaryAccount)->create([
        'published_at' => '2026-02-15 12:00:00',
        'engagement_rate' => 99.00,
    ]);

    InstagramMedia::factory()->for($outsiderAccount)->create([
        'published_at' => '2026-02-15 12:00:00',
        'engagement_rate' => 77.00,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('accountId', (string) $primaryAccount->id)
        ->assertViewHas('engagementTrend.values', [14.0, 10.0, 6.0])
        ->assertViewHas('engagementTrend.average', 9.0)
        ->set('period', '90_days')
        ->assertViewHas('engagementTrend.values', [20.0, 22.0, 12.0, 6.0])
        ->set('period', 'all')
        ->assertViewHas('engagementTrend.values', [21.0, 12.0, 6.0]);

    CarbonImmutable::setTestNow();
});
