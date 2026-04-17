<?php

declare(strict_types=1);

use App\Models\CuratorMedia;
use App\Models\User;
use App\Query\CuratorMediaScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::findOrCreate('View:CuratorMedia');
    Permission::findOrCreate('ViewOwn:CuratorMedia');
    Permission::findOrCreate('ViewAny:CuratorMedia');

    $app = app();
    $reflection = new ReflectionObject($app);
    $property = $reflection->getProperty('isRunningInConsole');

    $this->consoleProperty = $property;
    $this->originalConsoleState = $property->getValue($app);
});

afterEach(function (): void {
    auth()->logout();
    $this->consoleProperty->setValue(app(), $this->originalConsoleState);
});

function setConsoleStateForCuratorMediaScopeTest(bool $state): void
{
    test()->consoleProperty->setValue(app(), $state);
}

function applyScopeAndGetMediaIds(): array
{
    $builder = CuratorMedia::query()->withoutGlobalScopes();
    (new CuratorMediaScope())->apply($builder, new CuratorMedia);

    return $builder->pluck('id')->all();
}

it('skips filtering when application runs in console', function (): void {
    setConsoleStateForCuratorMediaScopeTest(true);

    $user = User::factory()->create();
    $user->givePermissionTo('ViewOwn:CuratorMedia');
    $this->actingAs($user);

    $mine = CuratorMedia::factory()->create(['created_by' => $user->id]);
    $other = CuratorMedia::factory()->create();

    $ids = applyScopeAndGetMediaIds();

    expect($ids)->toContain($mine->id, $other->id);
});

it('does not filter guest queries outside console mode', function (): void {
    setConsoleStateForCuratorMediaScopeTest(false);

    auth()->logout();

    $first = CuratorMedia::factory()->create();
    $second = CuratorMedia::factory()->create();

    $ids = applyScopeAndGetMediaIds();

    expect($ids)->toContain($first->id, $second->id);
});

it('does not filter for users with view permission', function (): void {
    setConsoleStateForCuratorMediaScopeTest(false);

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('View:CuratorMedia');
    $this->actingAs($viewer);

    $first = CuratorMedia::factory()->create();
    $second = CuratorMedia::factory()->create();

    $ids = applyScopeAndGetMediaIds();

    expect($ids)->toContain($first->id, $second->id);
});

it('filters to owner records for users with view own permission', function (): void {
    setConsoleStateForCuratorMediaScopeTest(false);

    $owner = User::factory()->create();
    $owner->givePermissionTo('ViewOwn:CuratorMedia');
    $this->actingAs($owner);

    $mine = CuratorMedia::factory()->create(['created_by' => $owner->id]);
    $other = CuratorMedia::factory()->create();

    $ids = applyScopeAndGetMediaIds();

    expect($ids)->toContain($mine->id)
        ->and($ids)->not->toContain($other->id)
        ->and($ids)->toHaveCount(1);
});

it('falls back to owner filter when user lacks view and view own permissions', function (): void {
    setConsoleStateForCuratorMediaScopeTest(false);

    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:CuratorMedia');
    $this->actingAs($user);

    $mine = CuratorMedia::factory()->create(['created_by' => $user->id]);
    $other = CuratorMedia::factory()->create();

    $ids = applyScopeAndGetMediaIds();

    expect($ids)->toContain($mine->id)
        ->and($ids)->not->toContain($other->id)
        ->and($ids)->toHaveCount(1);
});
