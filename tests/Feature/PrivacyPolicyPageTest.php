<?php

it('renders the privacy policy page', function (): void {
    $response = $this->get(route('privacy-policy'));

    $response->assertOk();
    $response->assertSeeText('Privacy Policy');
    $response->assertSeeText('Information We Collect');
    $response->assertSee('href="'.route('home').'#features"', false);
    $response->assertSee('href="'.route('terms').'"', false);
});

it('links to the privacy policy page from the landing footer', function (): void {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('href="'.route('privacy-policy').'"', false);
});
