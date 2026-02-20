<?php

use App\Enums\AnalyticsPeriod;
use App\Enums\AnalyticsTopContentSort;
use App\Enums\MediaType;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
});

it('scopes instagram media by user and client ownership helpers', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerAccount = SocialAccount::factory()->for($owner)->create();
    $outsiderAccount = SocialAccount::factory()->for($outsider)->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    $ownerCampaign = Campaign::factory()->for($ownerClient)->create(['name' => 'Owner Campaign']);
    $outsiderCampaign = Campaign::factory()->for($outsiderClient)->create(['name' => 'Outsider Campaign']);

    $ownerClientMedia = SocialAccountMedia::factory()->for($ownerAccount)->create();
    $ownerClientMedia->campaigns()->attach($ownerCampaign->id);

    $withoutClientMedia = SocialAccountMedia::factory()->for($ownerAccount)->create();

    $outsiderClientMedia = SocialAccountMedia::factory()->for($ownerAccount)->create();
    $outsiderClientMedia->campaigns()->attach($outsiderCampaign->id);

    $outsideUserMedia = SocialAccountMedia::factory()->for($outsiderAccount)->create();
    $outsideUserMedia->campaigns()->attach($outsiderCampaign->id);

    $forClientIds = SocialAccountMedia::query()
        ->forClient($ownerClient->id)
        ->pluck('id')
        ->all();

    $forClientOwnedByUserIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->forClientOwnedByUser($ownerClient->id, $owner->id)
        ->pluck('id')
        ->all();

    $withoutClientsIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->withoutClientsForUser($owner->id)
        ->pluck('id')
        ->all();

    $filteredWithoutClientsIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByClient('without_clients', $owner->id)
        ->pluck('id')
        ->all();

    $filteredOwnedClientIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByClient((string) $ownerClient->id, $owner->id)
        ->pluck('id')
        ->all();

    $allOwnerMediaIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByClient('all', $owner->id)
        ->pluck('id')
        ->all();

    expect($forClientIds)->toBe([$ownerClientMedia->id])
        ->and($forClientOwnedByUserIds)->toBe([$ownerClientMedia->id])
        ->and($withoutClientsIds)->toEqualCanonicalizing([$withoutClientMedia->id, $outsiderClientMedia->id])
        ->and($filteredWithoutClientsIds)->toEqualCanonicalizing([$withoutClientMedia->id, $outsiderClientMedia->id])
        ->and($filteredOwnedClientIds)->toBe([$ownerClientMedia->id])
        ->and($allOwnerMediaIds)->toEqualCanonicalizing([
            $ownerClientMedia->id,
            $withoutClientMedia->id,
            $outsiderClientMedia->id,
        ])
        ->and($allOwnerMediaIds)->not->toContain($outsideUserMedia->id);
});

it('filters instagram media by media type account and date window and sorts gallery results', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerPrimaryAccount = SocialAccount::factory()->for($owner)->create();
    $ownerSecondaryAccount = SocialAccount::factory()->for($owner)->create();
    $outsiderAccount = SocialAccount::factory()->for($outsider)->create();

    $matchingOlder = SocialAccountMedia::factory()->for($ownerPrimaryAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => '2026-02-11 10:00:00',
    ]);

    $matchingNewer = SocialAccountMedia::factory()->for($ownerPrimaryAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => '2026-02-14 10:00:00',
    ]);

    SocialAccountMedia::factory()->for($ownerPrimaryAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => '2026-02-16 10:00:00',
    ]);

    SocialAccountMedia::factory()->for($ownerPrimaryAccount)->create([
        'media_type' => MediaType::Post,
        'published_at' => '2026-02-12 10:00:00',
    ]);

    SocialAccountMedia::factory()->for($ownerSecondaryAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => '2026-02-12 10:00:00',
    ]);

    SocialAccountMedia::factory()->for($outsiderAccount)->create([
        'media_type' => MediaType::Reel,
        'published_at' => '2026-02-12 10:00:00',
    ]);

    $filteredIds = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByMediaType(MediaType::Reel->value)
        ->filterByAccount((string) $ownerPrimaryAccount->id)
        ->publishedFrom('2026-02-10')
        ->publishedUntil('2026-02-15')
        ->sortForGallery('published_at', 'asc')
        ->pluck('id')
        ->all();

    $ownerAllMediaCount = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->count();

    $invalidTypeCount = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByMediaType('unsupported-media-type')
        ->count();

    $allAccountCount = SocialAccountMedia::query()
        ->forUser($owner->id)
        ->filterByAccount('all')
        ->count();

    expect($filteredIds)->toBe([$matchingOlder->id, $matchingNewer->id])
        ->and($invalidTypeCount)->toBe($ownerAllMediaCount)
        ->and($allAccountCount)->toBe($ownerAllMediaCount);
});

it('orders instagram media newest first and chronologically with id tie breaking', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    $oldest = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-10 08:00:00',
    ]);

    $sameDayFirst = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-11 08:00:00',
    ]);

    $sameDaySecond = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-11 08:00:00',
    ]);

    $newest = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-12 08:00:00',
    ]);

    $latestIds = SocialAccountMedia::query()
        ->forUser($user->id)
        ->latestPublished()
        ->pluck('id')
        ->all();

    $chronologicalIds = SocialAccountMedia::query()
        ->forUser($user->id)
        ->publishedChronologically()
        ->pluck('id')
        ->all();

    expect($latestIds[0])->toBe($newest->id)
        ->and($latestIds[count($latestIds) - 1])->toBe($oldest->id)
        ->and($chronologicalIds)->toBe([
            $oldest->id,
            $sameDayFirst->id,
            $sameDaySecond->id,
            $newest->id,
        ]);
});

it('deduplicates rows with distinct media rows helper after joins', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();

    $campaignOne = Campaign::factory()->for($client)->create(['name' => 'Campaign One']);
    $campaignTwo = Campaign::factory()->for($client)->create(['name' => 'Campaign Two']);

    $media = SocialAccountMedia::factory()->for($account)->create();
    $media->campaigns()->attach([$campaignOne->id, $campaignTwo->id]);

    $joinedCount = SocialAccountMedia::query()
        ->join('campaign_media', 'instagram_media.id', '=', 'campaign_media.instagram_media_id')
        ->where('instagram_media.id', $media->id)
        ->count();

    $distinctRows = SocialAccountMedia::query()
        ->join('campaign_media', 'instagram_media.id', '=', 'campaign_media.instagram_media_id')
        ->where('instagram_media.id', $media->id)
        ->distinctMediaRows()
        ->get();

    expect($joinedCount)->toBe(2)
        ->and($distinctRows)->toHaveCount(1)
        ->and($distinctRows->first()->id)->toBe($media->id);
});

it('eager loads instagram account and client filtered campaigns for media records', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $account = SocialAccount::factory()->for($owner)->create();
    $media = SocialAccountMedia::factory()->for($account)->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    $ownerCampaign = Campaign::factory()->for($ownerClient)->create(['name' => 'Owner Campaign']);
    $outsiderCampaign = Campaign::factory()->for($outsiderClient)->create(['name' => 'Outsider Campaign']);

    $media->campaigns()->attach([$ownerCampaign->id, $outsiderCampaign->id]);

    $resolved = SocialAccountMedia::query()
        ->whereKey($media->id)
        ->withSocialAccount()
        ->withCampaignsForClient($ownerClient->id)
        ->firstOrFail();

    $campaignAttributes = $resolved->campaigns->first()?->getAttributes() ?? [];

    expect($resolved->relationLoaded('socialAccount'))->toBeTrue()
        ->and($resolved->socialAccount?->id)->toBe($account->id)
        ->and($resolved->relationLoaded('campaigns'))->toBeTrue()
        ->and($resolved->campaigns->pluck('id')->all())->toBe([$ownerCampaign->id])
        ->and(array_key_exists('id', $campaignAttributes))->toBeTrue()
        ->and(array_key_exists('name', $campaignAttributes))->toBeTrue();
});

it('selects analytics summary columns for instagram media', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    $media = SocialAccountMedia::factory()->for($account)->create([
        'reach' => 4200,
        'impressions' => 5400,
        'engagement_rate' => 4.5,
        'like_count' => 999,
    ]);

    $summary = SocialAccountMedia::query()
        ->whereKey($media->id)
        ->forAnalyticsSummary()
        ->firstOrFail();

    $attributes = $summary->getAttributes();

    expect(array_key_exists('id', $attributes))->toBeTrue()
        ->and(array_key_exists('social_account_id', $attributes))->toBeTrue()
        ->and(array_key_exists('published_at', $attributes))->toBeTrue()
        ->and(array_key_exists('reach', $attributes))->toBeTrue()
        ->and(array_key_exists('impressions', $attributes))->toBeTrue()
        ->and(array_key_exists('engagement_rate', $attributes))->toBeTrue()
        ->and(array_key_exists('like_count', $attributes))->toBeFalse();
});

it('loads only campaigns owned by a user with client relation and media counts ordered by name', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $account = SocialAccount::factory()->for($owner)->create();
    $primaryMedia = SocialAccountMedia::factory()->for($account)->create();
    $secondaryMedia = SocialAccountMedia::factory()->for($account)->create();

    $ownerClientOne = Client::factory()->for($owner)->create();
    $ownerClientTwo = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    $betaCampaign = Campaign::factory()->for($ownerClientOne)->create(['name' => 'Beta']);
    $alphaCampaign = Campaign::factory()->for($ownerClientTwo)->create(['name' => 'Alpha']);
    $outsiderCampaign = Campaign::factory()->for($outsiderClient)->create(['name' => 'Outsider']);

    $primaryMedia->campaigns()->attach([$alphaCampaign->id, $betaCampaign->id, $outsiderCampaign->id]);
    $secondaryMedia->campaigns()->attach([$alphaCampaign->id]);

    $resolved = SocialAccountMedia::query()
        ->whereKey($primaryMedia->id)
        ->withOwnedCampaignsForUser($owner->id)
        ->firstOrFail();

    $names = $resolved->campaigns->pluck('name')->all();
    $counts = $resolved->campaigns
        ->mapWithKeys(fn (Campaign $campaign): array => [$campaign->name => $campaign->instagram_media_count])
        ->all();

    $allClientsLoaded = $resolved->campaigns->every(
        fn (Campaign $campaign): bool => $campaign->relationLoaded('client')
    );

    expect($names)->toBe(['Alpha', 'Beta'])
        ->and($counts['Alpha'])->toBe(2)
        ->and($counts['Beta'])->toBe(1)
        ->and($allClientsLoaded)->toBeTrue();
});

it('calculates account average metrics for recent media and defaults to zero values', function (): void {
    Carbon::setTestNow('2026-02-20 10:00:00');

    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $emptyAccount = SocialAccount::factory()->for($user)->create();

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-10 10:00:00',
        'like_count' => 100,
        'comments_count' => 10,
        'reach' => 1000,
        'engagement_rate' => 2.5,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-01-25 10:00:00',
        'like_count' => 300,
        'comments_count' => 30,
        'reach' => 3000,
        'engagement_rate' => 3.5,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2025-09-01 10:00:00',
        'like_count' => 9999,
        'comments_count' => 999,
        'reach' => 99999,
        'engagement_rate' => 99.9,
    ]);

    $averages = SocialAccountMedia::query()->accountAverageMetricsForRecentDays($account->id, 90);
    $emptyAverages = SocialAccountMedia::query()->accountAverageMetricsForRecentDays($emptyAccount->id, 90);

    expect($averages)->toBe([
        'likes' => 200.0,
        'comments' => 20.0,
        'reach' => 2000.0,
        'engagement_rate' => 3.0,
    ])->and($emptyAverages)->toBe([
        'likes' => 0.0,
        'comments' => 0.0,
        'reach' => 0.0,
        'engagement_rate' => 0.0,
    ]);
});

it('filters media by analytics period and sorts top performing results', function (): void {
    Carbon::setTestNow('2026-02-20 10:00:00');
    CarbonImmutable::setTestNow('2026-02-20 10:00:00');

    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2025-10-01 10:00:00',
        'reach' => 9000,
        'engagement_rate' => 30,
    ]);

    $recentLowerReach = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-10 10:00:00',
        'reach' => 800,
        'engagement_rate' => 9,
    ]);

    $recentHigherReachLowerEngagement = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-15 10:00:00',
        'reach' => 1200,
        'engagement_rate' => 2,
    ]);

    $recentHigherReachHigherEngagement = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-18 10:00:00',
        'reach' => 1200,
        'engagement_rate' => 5,
    ]);

    $reachSortedIds = SocialAccountMedia::query()
        ->forUser($user->id)
        ->forAnalyticsPeriod(AnalyticsPeriod::NinetyDays)
        ->topPerforming(AnalyticsTopContentSort::Reach)
        ->pluck('id')
        ->all();

    $topEngagementId = SocialAccountMedia::query()
        ->forUser($user->id)
        ->forAnalyticsPeriod(AnalyticsPeriod::NinetyDays)
        ->topPerforming(AnalyticsTopContentSort::Engagement)
        ->value('id');

    expect($reachSortedIds)->toBe([
        $recentHigherReachHigherEngagement->id,
        $recentHigherReachLowerEngagement->id,
        $recentLowerReach->id,
    ])->and($topEngagementId)->toBe($recentLowerReach->id);
});

it('builds daily engagement trend buckets for recent periods', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-10 09:00:00',
        'engagement_rate' => 2,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-10 17:00:00',
        'engagement_rate' => 4,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-11 11:00:00',
        'engagement_rate' => 6,
    ]);

    $trend = SocialAccountMedia::query()
        ->forUser($user->id)
        ->engagementTrend(AnalyticsPeriod::SevenDays)
        ->map(fn (array $bucket): array => [
            'date' => $bucket['date'],
            'value' => $bucket['value'],
        ])
        ->all();

    expect($trend)->toBe([
        ['date' => '2026-02-10', 'value' => 3.0],
        ['date' => '2026-02-11', 'value' => 6.0],
    ]);
});

it('builds weekly and monthly engagement trend buckets for longer periods', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-16 09:00:00',
        'engagement_rate' => 2,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-18 09:00:00',
        'engagement_rate' => 6,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-02-24 09:00:00',
        'engagement_rate' => 10,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'published_at' => '2026-03-05 09:00:00',
        'engagement_rate' => 8,
    ]);

    $weeklyTrend = SocialAccountMedia::query()
        ->forUser($user->id)
        ->engagementTrend(AnalyticsPeriod::NinetyDays)
        ->map(fn (array $bucket): array => [
            'date' => $bucket['date'],
            'value' => $bucket['value'],
        ])
        ->all();

    $monthlyTrend = SocialAccountMedia::query()
        ->forUser($user->id)
        ->engagementTrend(AnalyticsPeriod::AllTime)
        ->map(fn (array $bucket): array => [
            'date' => $bucket['date'],
            'value' => $bucket['value'],
        ])
        ->all();

    expect($weeklyTrend)->toBe([
        ['date' => '2026-02-16', 'value' => 4.0],
        ['date' => '2026-02-23', 'value' => 10.0],
        ['date' => '2026-03-02', 'value' => 8.0],
    ])->and($monthlyTrend)->toBe([
        ['date' => '2026-02-01', 'value' => 6.0],
        ['date' => '2026-03-01', 'value' => 8.0],
    ]);
});

it('builds content type breakdown including empty media type defaults', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

    SocialAccountMedia::factory()->for($account)->create([
        'media_type' => MediaType::Post,
        'engagement_rate' => 4,
        'reach' => 100,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'media_type' => MediaType::Post,
        'engagement_rate' => 6,
        'reach' => 200,
    ]);

    SocialAccountMedia::factory()->for($account)->create([
        'media_type' => MediaType::Reel,
        'engagement_rate' => 8,
        'reach' => 300,
    ]);

    $breakdown = SocialAccountMedia::query()
        ->forUser($user->id)
        ->contentTypeBreakdown();

    expect($breakdown->keys()->all())->toBe([
        MediaType::Post->value,
        MediaType::Reel->value,
        MediaType::Story->value,
    ])->and($breakdown->get(MediaType::Post->value))->toBe([
        'count' => 2,
        'average_engagement_rate' => 5.0,
        'average_reach' => 150,
    ])->and($breakdown->get(MediaType::Reel->value))->toBe([
        'count' => 1,
        'average_engagement_rate' => 8.0,
        'average_reach' => 300,
    ])->and($breakdown->get(MediaType::Story->value))->toBe([
        'count' => 0,
        'average_engagement_rate' => 0.0,
        'average_reach' => 0,
    ]);
});
