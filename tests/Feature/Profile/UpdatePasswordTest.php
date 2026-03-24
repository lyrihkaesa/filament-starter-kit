<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Livewire\Profile\UpdatePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can update password via the profile page footer', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    // In Filament testing, we can't easily cross-component test actions that dispatch events to children in one go easily,
    // so we test the child's reaction to the event.

    Livewire::test(UpdatePassword::class)
        ->fillForm([
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->dispatch('update-password')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('validates password update requirements', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->fillForm([
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'mismatch',
        ])
        ->dispatch('update-password')
        ->assertHasErrors(['data.current_password', 'data.password']);

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});
