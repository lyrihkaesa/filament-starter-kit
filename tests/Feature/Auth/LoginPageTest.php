<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

it('filament login page use custom page', function (): void {
    $this->get('/app/login')
        ->assertSuccessful()
        ->assertSee('Login');
});

it('autofills login form when debug mode enabled', function (): void {
    // Aktifkan debug mode
    Config::set('app.debug', true);

    // Pastikan autofill email/password
    Livewire::test(Login::class)
        ->assertOk()
        ->assertSet('data.email', 'superadmin@example.com')
        ->assertSet('data.password', 'password')
        ->assertSet('data.remember', true);
});

it('does not autofill login form when debug mode disabled', function (): void {
    // Matikan debug mode
    Config::set('app.debug', false);

    // Pastikan autofill email/password
    Livewire::test(Login::class)
        ->assertOk()
        ->assertNotSet('data.email', 'superadmin@example.com')
        ->assertNotSet('data.password', 'password')
        ->assertSet('data.remember', false);
});
