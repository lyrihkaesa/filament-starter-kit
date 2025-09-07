<?php

declare(strict_types=1);

it('the root url redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

it('login route name redirects to login route filament page', function () {
    $response = $this->get('/login');

    $response->assertRedirect('/app/login');
});

it('the login page is accessible', function () {
    $response = $this->get('/app/login');

    $response->assertStatus(200);
});
