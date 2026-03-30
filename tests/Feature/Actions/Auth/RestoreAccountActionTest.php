<?php

declare(strict_types=1);

use App\Actions\Auth\RestoreAccountAction;
use App\Models\User;
use Illuminate\Support\Str;

it('can restore a soft-deleted user', function (): void {
    $user = User::factory()->create();
    $user->delete();

    $action = resolve(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeTrue();
    expect($user->fresh()->deleted_at)->toBeNull();
});

it('returns false for non-deleted user', function (): void {
    $user = User::factory()->create();

    $action = resolve(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeFalse();
});

it('returns false for non-existent user id', function (): void {
    $action = resolve(RestoreAccountAction::class);
    $result = $action->handle(Str::uuid()->toString());

    expect($result)->toBeFalse();
});

it('returns false for anonymized user', function (): void {
    $user = User::factory()->create();
    $user->anonymize();

    $action = resolve(RestoreAccountAction::class);
    $result = $action->handle($user->id);

    expect($result)->toBeFalse();
});
