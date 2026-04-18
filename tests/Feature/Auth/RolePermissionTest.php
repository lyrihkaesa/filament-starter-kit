<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    // Reset cached roles and permissions
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('can assign super_admin role and check permissions', function (): void {
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $permission = Permission::query()->firstOrCreate(['name' => 'ViewAny:Post', 'guard_name' => 'web']);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasRole('super_admin'))->toBeTrue()
        ->and($user->can('ViewAny:Post'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('ViewAny:Post'))->toBeTrue();
});

it('verifies that ShieldSeeder correctly sets up roles and permissions', function (): void {
    $this->seed(ShieldSeeder::class);

    $superAdmin = User::query()->where('email', 'superadmin@example.com')->first();

    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->hasRole('super_admin'))->toBeTrue()
        ->and($superAdmin->can('ViewAny:Post'))->toBeTrue();
});
