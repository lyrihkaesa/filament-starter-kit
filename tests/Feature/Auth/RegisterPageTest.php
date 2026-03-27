<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\Register;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Livewire\livewire;

it('can render register page', function () {
    $this->get(Filament::getRegistrationUrl())
        ->assertSuccessful()
        ->assertSee(__('filament-panels::auth/pages/register.heading'));
});

it('can register new user', function () {
    livewire(Register::class)
        ->set('data.name', 'Test User')
        ->set('data.email', 'test@example.com')
        ->set('data.password', 'password')
        ->set('data.passwordConfirmation', 'password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(Filament::getUrl());

    $this->assertAuthenticated();
    $this->assertDatabaseHas(User::class, [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});

it('can validate registration data', function () {
    livewire(Register::class)
        ->set('data.name', null)
        ->set('data.email', 'not-an-email')
        ->set('data.password', 'short')
        ->set('data.passwordConfirmation', 'different')
        ->call('register')
        ->assertHasErrors([
            'data.name',
            'data.email',
            'data.password',
        ]);

    $this->assertGuest();
});
