<?php

declare(strict_types=1);

use App\Actions\Users\DeleteUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can delete a user', function (): void {
    // Arrange
    $user = User::factory()->create();

    $action = resolve(DeleteUserAction::class);

    // Act
    $action->handle($user);

    // Assert
    // Pastikan user ter-soft delete
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});
