<?php

declare(strict_types=1);

use App\Actions\Auth\RequestRestoreAccountAction;
use App\Models\User;
use App\Notifications\Auth\RestoreAccountNotification;
use Illuminate\Support\Facades\Notification;

it('can send restoration notification to soft-deleted user', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $user->delete();

    $action = resolve(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeTrue();
    Notification::assertSentTo($user, RestoreAccountNotification::class);
});

it('can handle user object instead of email string', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $user->delete();

    $action = resolve(RequestRestoreAccountAction::class);
    $result = $action->handle($user);

    expect($result)->toBeTrue();
    Notification::assertSentTo($user, RestoreAccountNotification::class);
});

it('returns false for non-deleted user', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $action = resolve(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeFalse();
    Notification::assertNotSentTo($user, RestoreAccountNotification::class);
});

it('returns false for non-existent email', function (): void {
    Notification::fake();

    $action = resolve(RequestRestoreAccountAction::class);
    $result = $action->handle('nonexistent@example.com');

    expect($result)->toBeFalse();
});

it('returns false for anonymized user', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $user->anonymize();

    $action = resolve(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeFalse();
    Notification::assertNotSentTo($user, RestoreAccountNotification::class);
});
