<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

it('can assign super_admin role and check permissions', function () {
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'ViewAny:Post', 'guard_name' => 'web']);
    
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasRole('super_admin'))->toBeTrue()
        ->and($user->can('ViewAny:Post'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('ViewAny:Post'))->toBeTrue();
});

it('verifies that ShieldSeeder correctly sets up roles and permissions', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    $superAdmin = User::where('email', 'superadmin@example.com')->first();
    
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->hasRole('super_admin'))->toBeTrue()
        ->and($superAdmin->can('ViewAny:Post'))->toBeTrue();
});
