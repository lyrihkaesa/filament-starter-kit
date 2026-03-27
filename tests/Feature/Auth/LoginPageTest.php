<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

use function Pest\Livewire\livewire;

it('can render login page', function () {
    $this->get(Filament::getLoginUrl())
        ->assertSuccessful()
        ->assertSee(__('filament-panels::auth/pages/login.heading'));
});

it('can authenticate', function () {
    $user = User::factory()->create();

    livewire(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(Filament::getUrl());

    $this->assertAuthenticatedAs($user);
});

it('can validate login credentials', function () {
    livewire(Login::class)
        ->set('data.email', 'wrong@example.com')
        ->set('data.password', 'wrong-password')
        ->call('authenticate')
        ->assertHasErrors(['data.email']);

    $this->assertGuest();
});

it('autofills login form when debug mode enabled', function (): void {
    Config::set('app.debug', true);

    livewire(Login::class)
        ->assertOk()
        ->assertSet('data.email', 'superadmin@example.com')
        ->assertSet('data.password', 'password')
        ->assertSet('data.remember', true);
});

it('does not autofill login form when debug mode disabled', function (): void {
    Config::set('app.debug', false);

    livewire(Login::class)
        ->assertOk()
        ->assertNotSet('data.email', 'superadmin@example.com')
        ->assertNotSet('data.password', 'password')
        ->assertSet('data.remember', false);
});
