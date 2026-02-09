<?php

test('guest can view landing page and see auth CTAs', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee(route('login'))
        ->assertSee(route('register'))
        ->assertSeeText('Get Started')
        ->assertSeeText('Log In');
});

test('pricing plans display required follower ranges', function () {
    $response = $this->get(route('home'));

    $plans = [
        '< 1,000 followers',
        '1,000 – 10,000 followers',
        '10,001 – 99,999 followers',
        '100,000 – 300,000 followers',
        '300,000 – 600,000 followers',
        '> 600,000 followers',
    ];

    foreach ($plans as $range) {
        $response->assertSeeText($range);
    }
});
