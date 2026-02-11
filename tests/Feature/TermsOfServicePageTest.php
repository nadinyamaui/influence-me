<?php

it('renders the terms of service page', function (): void {
    $response = $this->get(route('terms'));

    $response->assertOk();
    $response->assertSeeText('Terms of Service');
    $response->assertSeeText('Account Eligibility');
    $response->assertSee('href="'.route('home').'#features"', false);
    $response->assertSee('href="'.route('privacy-policy').'"', false);
});

it('links to the terms of service page from the landing footer', function (): void {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('href="'.route('terms').'"', false);
    $response->assertSee('href="'.route('portal.login').'"', false);
});
