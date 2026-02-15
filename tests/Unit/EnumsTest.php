<?php

use App\Enums\AccountType;
use App\Enums\ClientType;
use App\Enums\DemographicType;
use App\Enums\InvoiceStatus;
use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Enums\SyncStatus;

it('defines all media type enum cases', function (): void {
    expect(MediaType::cases())->toHaveCount(3)
        ->and(MediaType::Post->value)->toBe('post')
        ->and(MediaType::Reel->value)->toBe('reel')
        ->and(MediaType::Story->value)->toBe('story');
});

it('provides media type filters including all option', function (): void {
    expect(MediaType::filters())->toBe([
        'all',
        'post',
        'reel',
        'story',
    ]);
});

it('parses image and carousel media as post', function (): void {
    expect(MediaType::parse(['media_type' => 'IMAGE']))->toBe(MediaType::Post)
        ->and(MediaType::parse(['media_type' => 'CAROUSEL_ALBUM']))->toBe(MediaType::Post);
});

it('parses reels media product type as reel', function (): void {
    expect(MediaType::parse([
        'media_type' => 'VIDEO',
        'media_product_type' => 'REELS',
    ]))->toBe(MediaType::Reel);
});

it('parses video permalink containing reel path as reel', function (): void {
    expect(MediaType::parse([
        'media_type' => 'VIDEO',
        'permalink' => 'https://instagram.com/reel/abc123',
    ]))->toBe(MediaType::Reel);
});

it('parses non-reel video as post', function (): void {
    expect(MediaType::parse([
        'media_type' => 'VIDEO',
        'media_product_type' => 'FEED',
        'permalink' => 'https://instagram.com/p/abc123',
    ]))->toBe(MediaType::Post);
});

it('parses unknown media type as post', function (): void {
    expect(MediaType::parse(['media_type' => 'STORY']))->toBe(MediaType::Post)
        ->and(MediaType::parse([]))->toBe(MediaType::Post);
});

it('defines all client type enum cases', function (): void {
    expect(ClientType::cases())->toHaveCount(2)
        ->and(ClientType::Brand->value)->toBe('brand')
        ->and(ClientType::Individual->value)->toBe('individual');
});

it('defines all proposal status enum cases', function (): void {
    expect(ProposalStatus::cases())->toHaveCount(5)
        ->and(ProposalStatus::Draft->value)->toBe('draft')
        ->and(ProposalStatus::Sent->value)->toBe('sent')
        ->and(ProposalStatus::Approved->value)->toBe('approved')
        ->and(ProposalStatus::Rejected->value)->toBe('rejected')
        ->and(ProposalStatus::Revised->value)->toBe('revised');
});

it('defines all invoice status enum cases', function (): void {
    expect(InvoiceStatus::cases())->toHaveCount(5)
        ->and(InvoiceStatus::Draft->value)->toBe('draft')
        ->and(InvoiceStatus::Sent->value)->toBe('sent')
        ->and(InvoiceStatus::Paid->value)->toBe('paid')
        ->and(InvoiceStatus::Overdue->value)->toBe('overdue')
        ->and(InvoiceStatus::Cancelled->value)->toBe('cancelled');
});

it('defines all scheduled post status enum cases', function (): void {
    expect(ScheduledPostStatus::cases())->toHaveCount(3)
        ->and(ScheduledPostStatus::Planned->value)->toBe('planned')
        ->and(ScheduledPostStatus::Published->value)->toBe('published')
        ->and(ScheduledPostStatus::Cancelled->value)->toBe('cancelled');
});

it('defines all demographic type enum cases', function (): void {
    expect(DemographicType::cases())->toHaveCount(4)
        ->and(DemographicType::Age->value)->toBe('age')
        ->and(DemographicType::Gender->value)->toBe('gender')
        ->and(DemographicType::City->value)->toBe('city')
        ->and(DemographicType::Country->value)->toBe('country');
});

it('defines all account type enum cases', function (): void {
    expect(AccountType::cases())->toHaveCount(2)
        ->and(AccountType::Business->value)->toBe('business')
        ->and(AccountType::Creator->value)->toBe('creator');
});

it('defines all sync status enum cases', function (): void {
    expect(SyncStatus::cases())->toHaveCount(3)
        ->and(SyncStatus::Idle->value)->toBe('idle')
        ->and(SyncStatus::Syncing->value)->toBe('syncing')
        ->and(SyncStatus::Failed->value)->toBe('failed');
});
