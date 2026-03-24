<?php

declare(strict_types=1);

use App\Actions\Users\UpdateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
