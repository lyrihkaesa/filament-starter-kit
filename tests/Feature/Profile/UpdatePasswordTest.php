<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can update password via the consolidated profile page', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], 'passwordForm')
        ->call('savePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('validates password update requirements on the consolidated page', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'mismatch',
        ], 'passwordForm')
        ->call('savePassword')
        ->assertHasErrors(['passwordData.current_password', 'passwordData.password']);

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});
