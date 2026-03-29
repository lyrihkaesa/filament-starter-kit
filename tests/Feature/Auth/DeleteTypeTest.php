<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Actions\Profile\DeleteUserAccountAction;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows restore hint when user deleted themselves', function (): void {
    $user = User::factory()->create([
        'email' => 'self-deleted@example.com',
        'password' => bcrypt('password'),
    ]);

    // Simulate self-deletion
    app(DeleteUserAccountAction::class)->handle($user, $user);

    expect($user->fresh()->isDeletedBySelf())->toBeTrue();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'self-deleted@example.com',
            'password' => 'password',
        ])
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
    app(DeleteUserAccountAction::class)->handle($user, $admin);

    expect($user->fresh()->isDeletedByAdmin())->toBeTrue();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'admin-deleted@example.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.email' => __('auth.deleted_by_admin')])
        ->assertSet('showRestoreAccountHint', false);
});
