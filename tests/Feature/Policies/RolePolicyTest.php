<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use Database\Seeders\ShieldSeeder;

beforeEach(function (): void {
    $this->seed(ShieldSeeder::class);
    $this->policy = new RolePolicy();
});

it('can view any role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('can view role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->view($user))->toBeTrue();
});

it('can create role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('can update role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->update($user))->toBeTrue();
});

it('can delete role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->delete($user))->toBeTrue();
});

it('can restore role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->restore($user))->toBeTrue();
});

it('can force delete role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->forceDelete($user))->toBeTrue();
});

it('can force delete any role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

it('can restore any role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

it('can replicate role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $role = Role::query()->first();

    expect($this->policy->replicate($user))->toBeTrue();
});

it('can reorder role with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->reorder($user))->toBeTrue();
});
