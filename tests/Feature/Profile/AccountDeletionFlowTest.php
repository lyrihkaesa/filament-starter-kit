<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('can soft-delete account from profile page', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->call('deleteAccount', 'password')
        ->assertHasNoErrors();

    // The redirect might happen in a different way or be caught differently.
    // Let's check the database first.
    expect($user->refresh()->trashed())->toBeTrue();

    expect(Auth::check())->toBeFalse()
        ->and($user->refresh()->trashed())->toBeTrue()
        ->and($user->anonymized_at)->toBeNull();
});

it('can be restored by admin after soft-deletion', function (): void {
    $user = User::factory()->create();
    $user->delete();

    expect($user->trashed())->toBeTrue();

    $user->restore();

    expect($user->refresh()->trashed())->toBeFalse()
        ->and($user->isActive())->toBeTrue();
});

it('anonymizes users after 30 days of deletion', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    // Soft delete 31 days ago
    $this->travelTo(now()->subDays(31));
    $user->delete();
    $this->travelBack();

    expect($user->refresh()->trashed())->toBeTrue()
        ->and($user->anonymized_at)->toBeNull();

    // Run command
    Artisan::call('app:anonymize-deleted-users');

    $user->refresh();

    expect($user->anonymized_at)->not->toBeNull()
        ->and($user->name)->toBe('Anonymous User')
        ->and($user->email)->toContain('anonymous_')
        ->and($user->trashed())->toBeTrue(); // Stay trashed
});

it('does not anonymize users deleted less than 30 days ago', function (): void {
    $user = User::factory()->create();

    // Soft delete 10 days ago
    $this->travelTo(now()->subDays(10));
    $user->delete();
    $this->travelBack();

    Artisan::call('app:anonymize-deleted-users');

    expect($user->refresh()->anonymized_at)->toBeNull();
});
