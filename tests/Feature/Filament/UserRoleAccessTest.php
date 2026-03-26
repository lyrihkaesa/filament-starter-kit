<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('disables roles field for users without Update:Role permission', function (): void {
    $user = User::factory()->create();

    // Give permission to view/update user, but NOT to update roles
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::create(['name' => 'ViewAny:User', 'guard_name' => 'web']));
    $role->givePermissionTo(Permission::create(['name' => 'View:User', 'guard_name' => 'web']));
    $role->givePermissionTo(Permission::create(['name' => 'Update:User', 'guard_name' => 'web']));

    $user->assignRole($role);
    $targetUser = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(EditUser::class, [
        'record' => $targetUser->getRouteKey(),
    ])
        ->assertFormFieldIsDisabled('roles');
});

it('enables roles field for users with Update:Role permission', function (): void {
    $user = User::factory()->create();

    // Give permission to view/update user AND update roles
    $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::create(['name' => 'ViewAny:User', 'guard_name' => 'web']));
    $role->givePermissionTo(Permission::create(['name' => 'View:User', 'guard_name' => 'web']));
    $role->givePermissionTo(Permission::create(['name' => 'Update:User', 'guard_name' => 'web']));
    $role->givePermissionTo(Permission::create(['name' => 'Update:Role', 'guard_name' => 'web']));

    $user->assignRole($role);
    $targetUser = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(EditUser::class, [
        'record' => $targetUser->getRouteKey(),
    ])
        ->assertFormFieldIsEnabled('roles');
});
