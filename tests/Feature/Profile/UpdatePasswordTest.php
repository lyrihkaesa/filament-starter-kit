<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('can update password via the consolidated profile page', function (): void {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('passwordData.current_password', 'old-password')
        ->set('passwordData.password', 'new-password')
        ->set('passwordData.password_confirmation', 'new-password')
        ->call('savePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('validates password update requirements on the consolidated page', function (): void {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('passwordData.current_password', 'wrong-password')
        ->set('passwordData.password', 'new-password')
        ->set('passwordData.password_confirmation', 'mismatch')
        ->call('savePassword')
        ->assertHasErrors(['passwordData.current_password', 'passwordData.password']);

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});
