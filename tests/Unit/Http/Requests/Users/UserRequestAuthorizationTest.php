<?php

declare(strict_types=1);

use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

function makeBoundRoute(UpdateUserRequest $request, mixed $parameter): Route
{
    $route = new Route('PATCH', '/api/v1/users/{user}', []);
    $route->bind($request);
    $route->setParameter('user', $parameter);

    return $route;
}

it('store user request denies when there is no authenticated user', function (): void {
    $request = StoreUserRequest::create('/api/v1/users', 'POST');
    $request->setContainer($this->app);
    $request->setUserResolver(static fn (): ?User => null);

    expect($request->authorize(User::factory()->make()))->toBeFalse();
});

it('store user request denies users without create permission', function (): void {
    $request = StoreUserRequest::create('/api/v1/users', 'POST');
    $request->setContainer($this->app);
    $request->setUserResolver(static fn (): User => User::factory()->create());

    expect($request->authorize(User::factory()->make()))->toBeFalse();
});

it('index user request handles token ability and policy checks', function (): void {
    $user = User::factory()->create();
    Permission::findOrCreate('ViewAny:User', 'web');
    $user->givePermissionTo('ViewAny:User');

    $request = IndexUserRequest::create('/api/v1/users', 'GET');

    expect($request->authorize($user))->toBeTrue();

    $deniedToken = $user->createToken('denied-users-read', ['users:create'])->accessToken;
    $user->withAccessToken($deniedToken);
    expect($request->authorize($user))->toBeFalse();

    $allowedToken = $user->createToken('allowed-users-read', ['users:read'])->accessToken;
    $user->withAccessToken($allowedToken);
    expect($request->authorize($user))->toBeTrue();
});

it('index user request returns rules and prepares default pagination', function (): void {
    $request = IndexUserRequest::create('/api/v1/users', 'GET');

    expect($request->rules())->toHaveKeys([
        'pagination',
        'per_page',
        'page',
        'cursor',
    ]);

    $method = new ReflectionMethod(IndexUserRequest::class, 'prepareForValidation');
    $method->invoke($request);

    expect($request->input('pagination'))->toBe('page');
});

it('update user request denies when the bound route parameter is not a user model', function (): void {
    $request = UpdateUserRequest::create('/api/v1/users/not-a-user', 'PATCH');
    $request->setContainer($this->app);
    $request->setUserResolver(static fn (): User => User::factory()->create());
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, 'not-a-user'));

    expect($request->authorize(User::factory()->make(), User::factory()->make()))->toBeFalse();
});

it('update user request denies when there is no authenticated user', function (): void {
    $target = User::factory()->create();
    $request = UpdateUserRequest::create('/api/v1/users/'.$target->getKey(), 'PATCH');
    $request->setContainer($this->app);
    $request->setUserResolver(static fn (): ?User => null);
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, $target));

    expect($request->authorize($target, User::factory()->make()))->toBeFalse();
});

it('update user request allows authorized users with update permission', function (): void {
    $authUser = User::factory()->create();
    $target = User::factory()->create();
    Permission::findOrCreate('Update:User', 'web');
    $authUser->givePermissionTo('Update:User');

    $request = UpdateUserRequest::create('/api/v1/users/'.$target->getKey(), 'PATCH');
    $request->setContainer($this->app);
    $request->setUserResolver(static fn (): User => $authUser);
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, $target));

    expect($request->authorize($target, $authUser))->toBeTrue();
});

it('store user request denies when token exists without users:create ability', function (): void {
    $user = User::factory()->create();
    Permission::findOrCreate('Create:User', 'web');
    $user->givePermissionTo('Create:User');

    $request = StoreUserRequest::create('/api/v1/users', 'POST');
    $request->setContainer($this->app);

    $deniedToken = $user->createToken('denied-users-create', ['users:read'])->accessToken;
    $user->withAccessToken($deniedToken);

    expect($request->authorize($user))->toBeFalse();
});

it('store user request denies role assignment without update role permission', function (): void {
    $user = User::factory()->create();
    Permission::findOrCreate('Create:User', 'web');
    $user->givePermissionTo('Create:User');

    $request = StoreUserRequest::create('/api/v1/users', 'POST', [
        'roles' => ['member'],
    ]);
    $request->setContainer($this->app);

    expect($request->authorize($user))->toBeFalse();
});

it('store user request allows role assignment with update role permission and returns rules', function (): void {
    $user = User::factory()->create();
    Permission::findOrCreate('Create:User', 'web');
    Permission::findOrCreate('Update:Role', 'web');
    $user->givePermissionTo(['Create:User', 'Update:Role']);
    $allowedToken = $user->createToken('allowed-users-create', ['users:create'])->accessToken;
    $user->withAccessToken($allowedToken);

    $request = StoreUserRequest::create('/api/v1/users', 'POST', [
        'roles' => ['member'],
    ]);
    $request->setContainer($this->app);

    expect($request->authorize($user))->toBeTrue()
        ->and($request->rules())->toHaveKeys([
            'name',
            'email',
            'password',
            'email_verified_at',
            'roles',
            'roles.*',
        ]);
});

it('update user request denies when token exists without users:update ability', function (): void {
    $authUser = User::factory()->create();
    $target = User::factory()->create();
    Permission::findOrCreate('Update:User', 'web');
    $authUser->givePermissionTo('Update:User');

    $request = UpdateUserRequest::create('/api/v1/users/'.$target->getKey(), 'PATCH');
    $request->setContainer($this->app);
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, $target));

    $deniedToken = $authUser->createToken('denied-users-update', ['users:read'])->accessToken;
    $authUser->withAccessToken($deniedToken);

    expect($request->authorize($target, $authUser))->toBeFalse();
});

it('update user request denies role assignment without update role permission', function (): void {
    $authUser = User::factory()->create();
    $target = User::factory()->create();
    Permission::findOrCreate('Update:User', 'web');
    $authUser->givePermissionTo('Update:User');

    $request = UpdateUserRequest::create('/api/v1/users/'.$target->getKey(), 'PATCH', [
        'roles' => ['member'],
    ]);
    $request->setContainer($this->app);
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, $target));

    expect($request->authorize($target, $authUser))->toBeFalse();
});

it('update user request allows role assignment with update role permission and returns rules', function (): void {
    $authUser = User::factory()->create();
    $target = User::factory()->create();
    Permission::findOrCreate('Update:User', 'web');
    Permission::findOrCreate('Update:Role', 'web');
    $authUser->givePermissionTo(['Update:User', 'Update:Role']);
    $allowedToken = $authUser->createToken('allowed-users-update', ['users:update'])->accessToken;
    $authUser->withAccessToken($allowedToken);

    $request = UpdateUserRequest::create('/api/v1/users/'.$target->getKey(), 'PATCH', [
        'roles' => ['member'],
    ]);
    $request->setContainer($this->app);
    $request->setRouteResolver(static fn (): Route => makeBoundRoute($request, $target));

    expect($request->authorize($target, $authUser))->toBeTrue()
        ->and($request->rules($target))->toHaveKeys([
            'name',
            'email',
            'password',
            'email_verified_at',
            'roles',
            'roles.*',
        ]);
});
