<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class TestingShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        /** @var array<string, list<string>> $rolesWithPermissions */
        $rolesWithPermissions = [
            'super_admin' => [
                'ViewAny:CuratorMedia', 'View:CuratorMedia', 'Create:CuratorMedia', 'Update:CuratorMedia', 'Delete:CuratorMedia',
                'Restore:CuratorMedia', 'ForceDelete:CuratorMedia', 'ForceDeleteAny:CuratorMedia', 'RestoreAny:CuratorMedia',
                'Replicate:CuratorMedia', 'Reorder:CuratorMedia', 'ViewOwn:CuratorMedia', 'UpdateOwn:CuratorMedia',
                'DeleteOwn:CuratorMedia', 'RestoreOwn:CuratorMedia', 'ForceDeleteOwn:CuratorMedia', 'DeleteUsed:CuratorMedia',
                'ForceDeleteUsed:CuratorMedia',
                'ViewAny:Activity', 'View:Activity', 'Create:Activity', 'Update:Activity', 'Delete:Activity', 'Restore:Activity',
                'ForceDelete:Activity', 'ForceDeleteAny:Activity', 'RestoreAny:Activity', 'Replicate:Activity', 'Reorder:Activity',
                'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post', 'Delete:Post', 'Restore:Post', 'ForceDelete:Post',
                'ForceDeleteAny:Post', 'RestoreAny:Post', 'Replicate:Post', 'Reorder:Post', 'ViewOwn:Post', 'UpdateOwn:Post',
                'DeleteOwn:Post', 'RestoreOwn:Post', 'ForceDeleteOwn:Post',
                'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', 'Restore:User', 'ForceDelete:User',
                'ForceDeleteAny:User', 'RestoreAny:User', 'Replicate:User', 'Reorder:User',
                'ViewAny:Role', 'View:Role', 'Create:Role', 'Update:Role', 'Delete:Role', 'Restore:Role', 'ForceDelete:Role',
                'ForceDeleteAny:Role', 'RestoreAny:Role', 'Replicate:Role', 'Reorder:Role',
                'View:StarterKitInfoWidget',
            ],
            'panel_user' => [],
            'admin' => [
                'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post', 'Delete:Post', 'Restore:Post', 'ForceDelete:Post',
                'ForceDeleteAny:Post', 'RestoreAny:Post', 'Replicate:Post', 'Reorder:Post',
                'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', 'Restore:User', 'ForceDelete:User',
                'ForceDeleteAny:User', 'RestoreAny:User', 'Replicate:User', 'Reorder:User',
                'ViewAny:CuratorMedia', 'View:CuratorMedia', 'Create:CuratorMedia', 'Update:CuratorMedia', 'Delete:CuratorMedia',
                'Restore:CuratorMedia',
            ],
            'member' => [
                'ViewAny:CuratorMedia', 'ViewOwn:CuratorMedia', 'UpdateOwn:CuratorMedia', 'DeleteOwn:CuratorMedia',
                'RestoreOwn:CuratorMedia',
                'ViewAny:Post', 'ViewOwn:Post', 'UpdateOwn:Post', 'DeleteOwn:Post', 'RestoreOwn:Post',
                'ViewAny:Activity', 'View:Activity',
            ],
        ];

        foreach ($rolesWithPermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            $permissionModels = [];
            foreach ($permissions as $permissionName) {
                $permissionModels[] = Permission::findOrCreate($permissionName, 'web');
            }

            $role->syncPermissions($permissionModels);
        }

        /** @var array<int, array{email: string, name: string, password: string, roles: list<string>}> $users */
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => 'password',
                'roles' => ['admin', 'panel_user'],
            ],
            [
                'email' => 'member@example.com',
                'name' => 'Member User',
                'password' => 'password',
                'roles' => ['member', 'panel_user'],
            ],
            [
                'email' => 'superadmin@example.com',
                'name' => 'Super Admin User',
                'password' => 'password',
                'roles' => ['super_admin', 'panel_user'],
            ],
        ];

        foreach ($users as $seededUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $seededUser['email']],
                [
                    'name' => $seededUser['name'],
                    'password' => $seededUser['password'],
                ],
            );

            $user->syncRoles($seededUser['roles']);
        }
    }
}
