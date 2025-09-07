<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

it('filament login page use custom page', function () {
    $this->get('/app/login')
        ->assertSeeLivewire(Login::class);
});

it('autofills login form when debug mode enabled', function () {
    // Aktifkan debug mode
    Config::set('app.debug', true);

    // Pastikan autofill email/password
    Livewire::test(Login::class)
        ->assertOk()
        ->assertSet('data.email', 'admin@example.com')
        ->assertSet('data.password', 'password')
        ->assertSet('data.remember', true);
});

it('does not autofill login form when debug mode disabled', function () {
    // Matikan debug mode
    Config::set('app.debug', false);

    // Pastikan autofill email/password
    Livewire::test(Login::class)
        ->assertOk()
        ->assertNotSet('data.email', 'admin@example.com')
        ->assertNotSet('data.password', 'password')
        ->assertSet('data.remember', false);
});
