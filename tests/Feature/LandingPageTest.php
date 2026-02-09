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
