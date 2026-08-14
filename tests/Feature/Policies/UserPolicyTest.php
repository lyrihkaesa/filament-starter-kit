<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\ShieldSeeder;

beforeEach(function (): void {
    $this->seed(ShieldSeeder::class);
    $this->policy = new UserPolicy();
});

it('can view any user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('cannot view any user without permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('member');

    expect($this->policy->viewAny($user))->toBeFalse();
});

it('can view user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->view($user))->toBeTrue();
});

it('can create user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('can update user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->update($user))->toBeTrue();
});

it('can delete user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->delete($user))->toBeTrue();
});

it('can restore user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->restore($user))->toBeTrue();
});

it('can force delete user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->forceDelete($user))->toBeTrue();
});

it('can force delete any user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

it('can restore any user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

it('can replicate user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->replicate($user))->toBeTrue();
});

it('can reorder user with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->reorder($user))->toBeTrue();
});
