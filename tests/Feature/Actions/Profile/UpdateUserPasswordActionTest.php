<?php

declare(strict_types=1);

use App\Actions\Profile\UpdateUserPasswordAction;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('can update user password', function (): void {
    // Arrange: Create a user with a known password
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $action = resolve(UpdateUserPasswordAction::class);

    // Act: Run the action to update password
    $action->handle($user, 'new-password');

    // Assert: Password is updated and hashed correctly
    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});
