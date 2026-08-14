<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('disables roles field for users without Update:Role permission', function (): void {
    $user = User::factory()->create();

    // Give permission to view/update user, but NOT to update roles
    $role = Role::findOrCreate('admin', 'web');
    $role->givePermissionTo(Permission::findOrCreate('ViewAny:User', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('View:User', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('Update:User', 'web'));

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
    $role = Role::findOrCreate('super_admin', 'web');
    $role->givePermissionTo(Permission::findOrCreate('ViewAny:User', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('View:User', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('Update:User', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('Update:Role', 'web'));

    $user->assignRole($role);
    $targetUser = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(EditUser::class, [
        'record' => $targetUser->getRouteKey(),
    ])
        ->assertFormFieldIsEnabled('roles');
});
