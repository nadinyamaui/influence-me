<?php

test('terms of service page is publicly accessible', function () {
    $response = $this->get(route('terms'));

    $response
        ->assertOk()
        ->assertSee('Terms of Service');
});
