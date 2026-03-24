<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can see the consolidated profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(EditProfile::getUrl())
        ->assertStatus(200)
        ->assertSee(__('Profile Information'))
        ->assertSee(__('Update Password'))
        ->assertSee(__('Browser Sessions'));
});

it('can update profile information', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => 'New Name',
            'email' => 'new@example.com',
        ], 'form')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh())
        ->name->toBe('New Name')
        ->email->toBe('new@example.com');
});
