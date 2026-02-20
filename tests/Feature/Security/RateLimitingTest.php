<?php

use Illuminate\Support\Facades\Route;

test('stripe webhook limiter allows sixty requests per minute per ip', function (): void {
    Route::post('/__test/stripe-webhook-rate-limit', fn () => response()->noContent())
        ->middleware('throttle:stripe-webhooks');

    foreach (range(1, 60) as $attempt) {
        $this->post('/__test/stripe-webhook-rate-limit')
            ->assertNoContent();
    }

    $this->post('/__test/stripe-webhook-rate-limit')
        ->assertStatus(429);
});
