<?php

use App\Exceptions\InstagramApiException;
use App\Exceptions\StripeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

it('renders a custom 403 error page', function (): void {
    Route::get('/_test-errors/403', fn () => abort(403));

    $this->get('/_test-errors/403')
        ->assertForbidden()
        ->assertSeeText('permission to access this page');
});

it('renders a custom 404 error page', function (): void {
    $this->get('/_test-errors/missing-page')
        ->assertNotFound()
        ->assertSee('Page not found');
});

it('renders a custom 500 error page', function (): void {
    Route::get('/_test-errors/500', fn () => abort(500));

    $this->get('/_test-errors/500')
        ->assertStatus(500)
        ->assertSee('Something went wrong');
});

it('renders a custom 503 error page', function (): void {
    Route::get('/_test-errors/503', fn () => abort(503));

    $this->get('/_test-errors/503')
        ->assertStatus(503)
        ->assertSeeText('right back');
});

it('reports instagram api exceptions to the instagram log channel', function (): void {
    $logger = \Mockery::mock();

    Log::shouldReceive('error')->zeroOrMoreTimes();

    Log::shouldReceive('channel')
        ->once()
        ->with('instagram')
        ->andReturn($logger);

    $logger->shouldReceive('error')
        ->once()
        ->with('Instagram sync failed', [
            'account_id' => 42,
            'endpoint' => '/me/media',
        ]);

    report(new InstagramApiException('Instagram sync failed', accountId: 42, endpoint: '/me/media'));
});

it('reports stripe exceptions to the stripe log channel', function (): void {
    $logger = \Mockery::mock();

    Log::shouldReceive('error')->zeroOrMoreTimes();

    Log::shouldReceive('channel')
        ->once()
        ->with('stripe')
        ->andReturn($logger);

    $logger->shouldReceive('error')
        ->once()
        ->with('Stripe payment session creation failed', [
            'invoice_id' => 1001,
        ]);

    report(new StripeException('Stripe payment session creation failed', invoiceId: 1001));
});

it('configures dedicated instagram and stripe logging channels', function (): void {
    expect(config('logging.channels.instagram.driver'))->toBe('daily')
        ->and(config('logging.channels.instagram.path'))->toContain('storage/logs/instagram.log')
        ->and(config('logging.channels.instagram.days'))->toBe(14)
        ->and(config('logging.channels.stripe.driver'))->toBe('daily')
        ->and(config('logging.channels.stripe.path'))->toContain('storage/logs/stripe.log')
        ->and(config('logging.channels.stripe.days'))->toBe(14);
});

it('configures horizon to process instagram-sync queue with dedicated worker settings', function (): void {
    expect(config('horizon.defaults.instagram-sync.queue'))->toBe(['instagram-sync'])
        ->and(config('horizon.defaults.instagram-sync.tries'))->toBe(3)
        ->and(config('horizon.defaults.instagram-sync.timeout'))->toBe(120)
        ->and(config('horizon.waits.redis:instagram-sync'))->toBe(120)
        ->and(array_key_exists('waits_notification_email', config('horizon')))->toBeTrue();
});
