<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Actions\Profile\DeleteUserAccountAction;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows restore hint when user deleted themselves', function (): void {
    $user = User::factory()->create([
        'email' => 'self-deleted@example.com',
        'password' => bcrypt('password'),
    ]);

    // Simulate self-deletion
    resolve(DeleteUserAccountAction::class)->handle($user, $user);

    expect($user->fresh()->isDeletedBySelf())->toBeTrue();

    Livewire::test(Login::class)
        ->set('data.email', 'self-deleted@example.com')
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['data.email' => __('auth.deleted')])
        ->assertSet('showRestoreAccountHint', true);
});

it('does not show restore hint when admin deleted the user', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'email' => 'admin-deleted@example.com',
        'password' => bcrypt('password'),
    ]);

    // Simulate admin-deletion
    resolve(DeleteUserAccountAction::class)->handle($user, $admin);

    expect($user->fresh()->isDeletedByAdmin())->toBeTrue();

    Livewire::test(Login::class)
        ->set('data.email', 'admin-deleted@example.com')
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['data.email' => __('auth.deleted_by_admin')])
        ->assertSet('showRestoreAccountHint', false);
});

it('anonymizes self-deleted users via command', function (): void {
    $user = User::factory()->create([
        'deleted_at' => now()->subDays(31),
        'deleted_by' => null, // Legacy/self
    ]);
    // Force set id as deleted_by for another case
    $user2 = User::factory()->create([
        'deleted_at' => now()->subDays(31),
    ]);
    $user2->forceFill(['deleted_by' => $user2->id])->saveQuietly();

    Artisan::call('app:anonymize-deleted-users');

    expect($user->fresh()->isAnonymous())->toBeTrue();
    expect($user2->fresh()->isAnonymous())->toBeTrue();
});

it('does not anonymize admin-deleted users via command', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'deleted_at' => now()->subDays(31),
    ]);
    $user->forceFill(['deleted_by' => $admin->id])->saveQuietly();

    Artisan::call('app:anonymize-deleted-users');

    expect($user->fresh()->isAnonymous())->toBeFalse();
    expect($user->fresh()->trashed())->toBeTrue();
});

it('anonymizes on forceDelete if self-deleted', function (): void {
    $user = User::factory()->create();
    $user->forceFill(['deleted_by' => $user->id])->saveQuietly();
    $user->refresh();
    $user->delete();

    $user->forceDelete();

    expect($user->fresh())->not->toBeNull();
    expect($user->fresh()->isAnonymous())->toBeTrue();
    expect($user->fresh()->trashed())->toBeTrue();
});

it('hard deletes on forceDelete if admin-deleted', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $user->forceFill(['deleted_by' => $admin->id])->saveQuietly();
    $user->refresh();

    $user->delete();
    $user->forceDelete();

    expect(User::withTrashed()->find($user->id))->toBeNull();
});
