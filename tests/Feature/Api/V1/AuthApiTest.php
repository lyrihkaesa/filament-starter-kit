<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function grantApiPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);
}

it('registers a user and returns a typed api token payload', function (): void {
    Role::create(['name' => 'member', 'guard_name' => 'web']);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Flutter User',
        'email' => 'flutter@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'pixel-8',
    ]);

    $response->assertCreated();

    $payload = $response->json();

    expect($payload['message'])->toBe('User registered successfully.')
        ->and($payload['data']['token'])->toBeString()
        ->and($payload['data']['token_type'])->toBe('Bearer')
        ->and($payload['data']['abilities'])->toBe(['profile:read'])
        ->and($payload['data']['user']['id'])->toBeString()
        ->and($payload['data']['user']['name'])->toBe('Flutter User')
        ->and($payload['data']['user']['avatar'])->toBeNull()
        ->and(array_key_exists('errors', $payload))->toBeFalse();

    expect(User::query()->where('email', 'flutter@example.com')->firstOrFail()->hasRole('member'))->toBeTrue();
    expect(PersonalAccessToken::query()->count())->toBe(1);
});

it('validates register requests with json errors', function (): void {
    $response = $this->postJson('/api/v1/register', []);

    $response->assertUnprocessable();

    expect($response->json('message'))->toBe('The given data was invalid.')
        ->and($response->json('errors.name.0'))->toBeString()
        ->and($response->json('errors.email.0'))->toBeString()
        ->and($response->json('errors.password.0'))->toBeString()
        ->and(array_key_exists('data', $response->json()))->toBeFalse();
});

it('logs a user in and returns token abilities', function (): void {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
    ]);

    grantApiPermissions($user, [
        'ViewAny:User',
        'Create:User',
        'Update:User',
        'Delete:User',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
        'device_name' => 'iphone-15',
    ]);

    $response->assertSuccessful();

    expect($response->json('message'))->toBe('Login successful.')
        ->and($response->json('data.token'))->toBeString()
        ->and($response->json('data.abilities'))->toBe([
            'profile:read',
            'users:read',
            'users:create',
            'users:update',
            'users:delete',
        ])
        ->and(array_key_exists('errors', $response->json()))->toBeFalse();
});

it('rejects login for an unknown email address', function (): void {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'missing@example.com',
        'password' => 'password123',
    ]);

    $response->assertUnauthorized();

    expect($response->json('message'))->toBe('Invalid credentials.')
        ->and($response->json('errors.email.0'))->toBe('The provided credentials are incorrect.')
        ->and(array_key_exists('data', $response->json()))->toBeFalse();
});

it('rejects login for a wrong password', function (): void {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized();

    expect($response->json('errors.email.0'))->toBe('The provided credentials are incorrect.');
});

it('returns the authenticated user for tokens with profile access', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['profile:read']);

    $response = $this->getJson('/api/v1/me');

    $response->assertSuccessful();

    expect($response->json('message'))->toBe('Authenticated user retrieved successfully.')
        ->and($response->json('data.id'))->toBe((string) $user->getKey())
        ->and($response->json('data.email'))->toBe($user->email)
        ->and(array_key_exists('errors', $response->json()))->toBeFalse();
});

it('returns json unauthenticated responses for protected auth endpoints', function (): void {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();

    expect($response->json())->toBe([
        'message' => 'Unauthenticated.',
        'errors' => [
            'auth' => ['Authentication is required to access this resource.'],
        ],
    ]);
});

it('forbids tokens without the profile read ability', function (): void {
    Sanctum::actingAs(User::factory()->create(), []);

    $response = $this->getJson('/api/v1/me');

    $response->assertForbidden();

    expect($response->json('message'))->toBe('This action is unauthorized.')
        ->and($response->json('errors.authorization.0'))->toBe('Missing required token ability.');
});

it('revokes only the current access token on logout', function (): void {
    $user = User::factory()->create();
    $firstToken = $user->createToken('device-a', ['profile:read']);
    $secondToken = $user->createToken('device-b', ['profile:read']);

    $response = $this->withHeader('Authorization', 'Bearer '.$firstToken->plainTextToken)
        ->postJson('/api/v1/logout');

    $response->assertSuccessful();

    expect($response->json())->toBe([
        'message' => 'Logout successful.',
    ]);

    expect(PersonalAccessToken::query()->whereKey($firstToken->accessToken->getKey())->exists())->toBeFalse()
        ->and(PersonalAccessToken::query()->whereKey($secondToken->accessToken->getKey())->exists())->toBeTrue();
});
