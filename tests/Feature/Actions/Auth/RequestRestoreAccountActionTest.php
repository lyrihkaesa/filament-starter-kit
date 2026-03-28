<?php

declare(strict_types=1);

use App\Actions\Auth\RequestRestoreAccountAction;
use App\Models\User;
use App\Notifications\Auth\RestoreAccountNotification;
use Illuminate\Support\Facades\Notification;

it('can send restoration notification to soft-deleted user', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->delete();

    $action = app(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeTrue();
    Notification::assertSentTo($user, RestoreAccountNotification::class);
});

it('can handle user object instead of email string', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->delete();

    $action = app(RequestRestoreAccountAction::class);
    $result = $action->handle($user);

    expect($result)->toBeTrue();
    Notification::assertSentTo($user, RestoreAccountNotification::class);
});

it('returns false for non-deleted user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $action = app(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeFalse();
    Notification::assertNotSentTo($user, RestoreAccountNotification::class);
});

it('returns false for non-existent email', function () {
    Notification::fake();

    $action = app(RequestRestoreAccountAction::class);
    $result = $action->handle('nonexistent@example.com');

    expect($result)->toBeFalse();
});

it('returns false for anonymized user', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->anonymize();

    $action = app(RequestRestoreAccountAction::class);
    $result = $action->handle($user->email);

    expect($result)->toBeFalse();
    Notification::assertNotSentTo($user, RestoreAccountNotification::class);
});
