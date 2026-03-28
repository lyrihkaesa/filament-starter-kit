<?php

declare(strict_types=1);

use App\Actions\Auth\RestoreAccountAction;
use App\Models\User;

it('can restore a soft-deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    $action = app(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeTrue();
    expect($user->fresh()->deleted_at)->toBeNull();
});

it('returns false for non-deleted user', function () {
    $user = User::factory()->create();

    $action = app(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeFalse();
});

it('returns false for non-existent user id', function () {
    $action = app(RestoreAccountAction::class);
    $result = $action->handle('non-existent-uuid');

    expect($result)->toBeFalse();
});

it('returns false for anonymized user', function () {
    $user = User::factory()->create();
    $user->anonymize();

    $action = app(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeFalse();
});
