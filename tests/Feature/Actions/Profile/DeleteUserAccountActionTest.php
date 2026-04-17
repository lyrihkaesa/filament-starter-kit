<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\DeleteUserAccountAction;
use App\Models\User;

it('can delete user account passing user as deleter', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $action = new DeleteUserAccountAction();
    $action->handle($user, $admin);

    expect($user->refresh()->trashed())->toBeTrue();
    expect($user->deleted_by)->toBe($admin->id);
});

it('can delete user account passing string as deleter', function (): void {
    $user = User::factory()->create();
    $adminId = (string) \Illuminate\Support\Str::uuid();

    $action = new DeleteUserAccountAction();
    $action->handle($user, $adminId);

    expect($user->refresh()->trashed())->toBeTrue();
    expect((string) $user->deleted_by)->toBe($adminId);
});

it('defaults deleter to user id when not provided', function (): void {
    $user = User::factory()->create();

    $action = new DeleteUserAccountAction();
    $action->handle($user);

    expect($user->refresh()->trashed())->toBeTrue();
    expect($user->deleted_by)->toBe($user->id);
});
