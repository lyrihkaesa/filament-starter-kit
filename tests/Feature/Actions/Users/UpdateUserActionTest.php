<?php

declare(strict_types=1);

use App\Actions\Users\UpdateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('can update a user', function (): void {
    // Arrange
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $data = [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ];

    $action = resolve(UpdateUserAction::class);

    // Act
    $updatedUser = $action->handle($user, $data);

    // Assert
    expect($updatedUser)->toBeInstanceOf(User::class)
        ->and($updatedUser->name)->toBe('New Name')
        ->and($updatedUser->email)->toBe('new@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'new@example.com',
    ]);
});

it('can update a user and sync roles', function (): void {
    // Arrange
    $user = User::factory()->create();

    // Create roles for testing
    $role1 = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role2 = Role::query()->firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $data = [
        'name' => 'New Name',
        'roles' => [$role1->id, $role2->id],
    ];

    $action = resolve(UpdateUserAction::class);

    // Act
    $updatedUser = $action->handle($user, $data);

    // Assert
    expect($updatedUser->name)->toBe('New Name');

    // Check if roles were synced correctly
    expect($updatedUser->hasRole('admin'))->toBeTrue()
        ->and($updatedUser->hasRole('editor'))->toBeTrue();
});

it('returns the original user model if fresh() returns null', function (): void {
    // Arrange
    $user = User::factory()->create();

    $action = resolve(UpdateUserAction::class);

    // Act
    // We delete the user from the database directly so that $user->fresh() returns null
    DB::table('users')->where('id', $user->id)->delete();

    $result = $action->handle($user, ['name' => 'New Name']);

    // Assert
    expect($result)->toBe($user);
});
