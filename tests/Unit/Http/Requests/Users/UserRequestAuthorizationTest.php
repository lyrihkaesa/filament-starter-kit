<?php

declare(strict_types=1);

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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
