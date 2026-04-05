<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\RestoreUserAccountAction;
use App\Models\User;

it('can restore user account', function (): void {
    $user = User::factory()->create();
    $user->delete();

    expect($user->trashed())->toBeTrue();

    $action = new RestoreUserAccountAction();
    $action->handle($user);

    expect($user->refresh()->trashed())->toBeFalse();
});
