<?php

use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('landing page renders for guests', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Turn creator momentum into a repeatable business system.');
});

test('landing page remains available for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Feature coverage from content to cash flow');
});

test('landing page includes required sections in order with auth call to actions', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSeeInOrder([
            'Turn creator momentum into a repeatable business system.',
            'Proof in progress, built transparently',
            'Why influencers switch to one operating system',
            'Feature coverage from content to cash flow',
            'How it works',
            'Simple pricing by Instagram audience size',
            'Frequently asked questions',
            'Ready to run your influence business with less overhead?',
        ]);

    $content = $response->getContent();

    expect(substr_count($content, 'href="'.route('login').'"'))->toBeGreaterThanOrEqual(3)
        ->and(substr_count($content, 'href="'.route('register').'"'))->toBeGreaterThanOrEqual(3);

    $response
        ->assertSee('Create Account')
        ->assertSee('Get Started')
        ->assertSee('Log In');
});

test('landing page displays all pricing tiers with correct follower ranges', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Fewer than 1,000 followers')
        ->assertSee('1,000 to 10,000 followers')
        ->assertSee('10,001 to 99,999 followers')
        ->assertSee('100,000 to 300,000 followers')
        ->assertSee('300,000 to 600,000 followers')
        ->assertSee('More than 600,000 followers');

    $response
        ->assertSee('$0')
        ->assertSee('$25')
        ->assertSee('$49')
        ->assertSee('$75')
        ->assertSee('$100')
        ->assertSee('Talk to us');
});

test('landing page includes all benefits in user-facing language', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Save hours every week')
        ->assertSee('Send proposals and invoices faster')
        ->assertSee('Keep clients confident')
        ->assertSee('Make smarter growth decisions');
});

test('landing page FAQ section contains required questions', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Can I cancel anytime?')
        ->assertSee('Do you support annual billing?')
        ->assertSee('Is there a free trial?')
        ->assertSee('Can clients access a portal without full accounts?');
});

test('landing page is responsive with mobile-friendly markup', function () {
    $response = $this->get(route('home'));

    $content = $response->getContent();

    // Verify responsive grid classes exist
    expect($content)->toContain('sm:grid-cols-2')
        ->toContain('lg:grid-cols-')
        ->toContain('md:grid-cols-')
        ->toContain('md:flex');
});
