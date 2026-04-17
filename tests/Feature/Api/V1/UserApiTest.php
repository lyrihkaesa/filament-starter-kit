<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function grantUserApiPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);
}

it('returns paginated users by default with typed metadata and can flags', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['ViewAny:User', 'Create:User', 'View:User']);
    User::factory()->count(20)->create();

    Sanctum::actingAs($admin, ['users:read', 'users:create']);

    $response = $this->getJson('/api/v1/users?per_page=5');

    $response->assertSuccessful();

    $meta = $response->json('meta');
    $firstUser = $response->json('data.0');

    expect($meta['pagination_type'])->toBe('page')
        ->and($meta['current_page'])->toBeInt()
        ->and($meta['per_page'])->toBeInt()
        ->and($meta['total'])->toBeInt()
        ->and($meta['last_page'])->toBeInt()
        ->and($meta['has_more_pages'])->toBeBool()
        ->and($meta['can']['create'])->toBeTrue()
        ->and($firstUser['id'])->toBeString()
        ->and($firstUser['name'])->toBeString()
        ->and($firstUser['avatar_url'])->toBeNull()
        ->and($firstUser['can']['view'])->toBeTrue()
        ->and($firstUser['can']['update'])->toBeFalse()
        ->and($firstUser['can']['delete'])->toBeFalse();
});

it('returns cursor pagination when requested', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['ViewAny:User']);
    User::factory()->count(6)->create();

    Sanctum::actingAs($admin, ['users:read']);

    $response = $this->getJson('/api/v1/users?pagination=cursor&per_page=2');

    $response->assertSuccessful();

    $meta = $response->json('meta');

    expect($meta['pagination_type'])->toBe('cursor')
        ->and($meta['per_page'])->toBeInt()
        ->and($meta['has_more_pages'])->toBeBool()
        ->and($meta['next_cursor'])->toBeString()
        ->and($meta['prev_cursor'])->toBeNull()
        ->and($meta['can']['create'])->toBeFalse();

    expect(array_key_exists('current_page', $meta))->toBeFalse();
});

it('keeps the list item resource shape identical for page and cursor modes', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['ViewAny:User']);
    User::factory()->count(4)->create();

    Sanctum::actingAs($admin, ['users:read']);

    $pageResponse = $this->getJson('/api/v1/users?pagination=page&per_page=2');
    $cursorResponse = $this->getJson('/api/v1/users?pagination=cursor&per_page=2');

    expect(array_keys($pageResponse->json('data.0')))->toBe(array_keys($cursorResponse->json('data.0')));
});

it('validates unsupported pagination types', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['ViewAny:User']);

    Sanctum::actingAs($admin, ['users:read']);

    $response = $this->getJson('/api/v1/users?pagination=offset');

    $response->assertUnprocessable();

    expect($response->json('errors.pagination.0'))->toBeString();
});

it('creates a user with the correct rest status code', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['Create:User', 'View:User', 'Update:Role']);
    Role::findOrCreate('member', 'web');

    Sanctum::actingAs($admin, ['users:create', 'users:read']);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'API User',
        'email' => 'api-user@example.com',
        'password' => 'password123',
        'roles' => ['member'],
    ]);

    $response->assertCreated();

    expect($response->json('message'))->toBe('User created successfully.')
        ->and($response->json('data.id'))->toBeString()
        ->and($response->json('data.email'))->toBe('api-user@example.com')
        ->and($response->json('data.can.view'))->toBeTrue()
        ->and($response->json('data.can.update'))->toBeFalse()
        ->and($response->json('data.can.delete'))->toBeFalse();
});

it('rejects role assignment when the caller cannot manage roles during creation', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['Create:User']);
    Role::findOrCreate('member', 'web');

    Sanctum::actingAs($admin, ['users:create']);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'API User',
        'email' => 'api-user@example.com',
        'password' => 'password123',
        'roles' => ['member'],
    ]);

    $response->assertForbidden();
});

it('shows a user when policy and token ability both allow it', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    grantUserApiPermissions($admin, ['View:User', 'Update:User']);

    Sanctum::actingAs($admin, ['users:read', 'users:update']);

    $response = $this->getJson('/api/v1/users/'.$target->getKey());

    $response->assertSuccessful();

    expect($response->json('data.id'))->toBe((string) $target->getKey())
        ->and($response->json('data.can.view'))->toBeTrue()
        ->and($response->json('data.can.update'))->toBeTrue()
        ->and($response->json('data.can.delete'))->toBeFalse();
});

it('forbids show requests without the required token ability', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    grantUserApiPermissions($admin, ['View:User']);

    Sanctum::actingAs($admin, []);

    $response = $this->getJson('/api/v1/users/'.$target->getKey());

    $response->assertForbidden();

    expect($response->json('errors.authorization.0'))->toBe('Missing required token ability.');
});

it('forbids list requests when the policy denies access', function (): void {
    Sanctum::actingAs(User::factory()->create(), ['users:read']);

    $response = $this->getJson('/api/v1/users');

    $response->assertForbidden();
});

it('updates a user and keeps response typing stable', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    grantUserApiPermissions($admin, ['View:User', 'Update:User']);

    Sanctum::actingAs($admin, ['users:read', 'users:update']);

    $response = $this->patchJson('/api/v1/users/'.$target->getKey(), [
        'name' => 'Updated Name',
    ]);

    $response->assertSuccessful();

    expect($response->json('data.name'))->toBe('Updated Name')
        ->and($response->json('data.id'))->toBeString()
        ->and($response->json('data.can.view'))->toBeTrue()
        ->and($response->json('data.can.update'))->toBeTrue()
        ->and($response->json('data.can.delete'))->toBeFalse();
});

it('rejects role assignment when the caller cannot manage roles during update', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    grantUserApiPermissions($admin, ['Update:User']);
    Role::findOrCreate('member', 'web');

    Sanctum::actingAs($admin, ['users:update']);

    $response = $this->patchJson('/api/v1/users/'.$target->getKey(), [
        'roles' => ['member'],
    ]);

    $response->assertForbidden();
});

it('returns validation errors for invalid user updates', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    $other = User::factory()->create();
    grantUserApiPermissions($admin, ['Update:User']);

    Sanctum::actingAs($admin, ['users:update']);

    $response = $this->patchJson('/api/v1/users/'.$target->getKey(), [
        'email' => $other->email,
    ]);

    $response->assertUnprocessable();

    expect($response->json('errors.email.0'))->toBeString();
});

it('deletes a user with a 200 response body', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    grantUserApiPermissions($admin, ['Delete:User']);

    Sanctum::actingAs($admin, ['users:delete']);

    $response = $this->deleteJson('/api/v1/users/'.$target->getKey());

    $response->assertSuccessful();

    expect($response->json())->toBe([
        'message' => 'User deleted successfully.',
    ]);

    $this->assertSoftDeleted('users', ['id' => $target->getKey()]);
});

it('returns json for missing users and unknown unversioned routes', function (): void {
    $admin = User::factory()->create();
    grantUserApiPermissions($admin, ['View:User']);

    Sanctum::actingAs($admin, ['users:read']);

    $missingUserResponse = $this->getJson('/api/v1/users/not-a-real-uuid');
    $missingRouteResponse = $this->getJson('/api/users');

    $missingUserResponse->assertNotFound();
    $missingRouteResponse->assertNotFound();

    expect($missingUserResponse->json())->toBe([
        'message' => 'Resource not found.',
        'errors' => [
            'resource' => ['The requested resource could not be found.'],
        ],
    ])->and($missingRouteResponse->json())->toBe([
        'message' => 'Resource not found.',
        'errors' => [
            'resource' => ['The requested resource could not be found.'],
        ],
    ]);
});

it('returns json unauthenticated responses for protected user endpoints', function (): void {
    $response = $this->getJson('/api/v1/users');

    $response->assertUnauthorized();

    expect($response->json('message'))->toBe('Unauthenticated.');
});
