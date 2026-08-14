<?php

declare(strict_types=1);

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutCurrentTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\Models\PersonalAccessToken;
use App\Models\Role;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

it('assigns the member role during registration when it exists', function (): void {
    Role::query()->firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = resolve(RegisterUserAction::class)->handle([
        'name' => 'Mobile User',
        'email' => 'mobile@example.com',
        'password' => 'password123',
    ]);

    expect($user->hasRole('member'))->toBeTrue();
});

it('registers a user without roles when the member role does not exist', function (): void {
    Role::query()->where('name', 'member')->delete();
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    $user = resolve(RegisterUserAction::class)->handle([
        'name' => 'Mobile User',
        'email' => 'mobile@example.com',
        'password' => 'password123',
    ]);

    expect($user->roles)->toBeEmpty();
});

it('creates a token with the abilities provided by the controller layer', function (): void {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $token = resolve(LoginUserAction::class)->handle($user, 'password123', 'flutter-phone', ['profile:read', 'users:read']);

    expect($token)->toBeString()
        ->and(PersonalAccessToken::query()->count())->toBe(1)
        ->and(PersonalAccessToken::query()->firstOrFail()->abilities)->toBe(['profile:read', 'users:read']);
});

it('returns null when login credentials are invalid', function (): void {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $result = resolve(LoginUserAction::class)->handle($user, 'wrong-password', 'flutter-phone', ['profile:read']);

    expect($result)->toBeNull();
});

it('revokes the current access token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('flutter-phone', ['profile:read']);

    $user->withAccessToken($token->accessToken);

    resolve(LogoutCurrentTokenAction::class)->handle($user);

    expect(PersonalAccessToken::query()->count())->toBe(0);
});
