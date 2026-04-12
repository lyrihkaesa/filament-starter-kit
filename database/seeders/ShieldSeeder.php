<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[{"id":"019d25e9-1f96-708b-aad1-8a7b3cbeb1d1","name":"Admin User","avatar_curator_id":null,"email":"admin@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:08.000000Z","updated_at":"2026-03-25T16:52:08.000000Z","deleted_at":null,"deleted_by":null,"anonymized_at":null,"password":"$2y$12$h7dlYkXJ8qLlucs9jHWCjOVsEa0liNPyVTPfyF9.iQ5zQS9BPppxa","roles":["admin","panel_user"],"permissions":[]},{"id":"019d25e9-20b3-701f-b8b0-3aca35664121","name":"Member User","avatar_curator_id":null,"email":"member@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:08.000000Z","updated_at":"2026-03-25T16:52:08.000000Z","deleted_at":null,"deleted_by":null,"anonymized_at":null,"roles":["member","panel_user"],"permissions":[],"password":"$2y$12$EHYU\\/ORYCKOsFesotFF\\/NOp7byazG..fGGG3CjCoZ\\/Srh2GMLSs6u"},{"id":"019d25e9-1e6f-71cf-b3e5-53f37b334dfd","name":"Super Admin User","avatar_curator_id":null,"email":"superadmin@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:07.000000Z","updated_at":"2026-03-25T16:52:07.000000Z","deleted_at":null,"deleted_by":null,"anonymized_at":null,"roles":["super_admin","panel_user"],"permissions":[],"password":"$2y$12$2cGoCTu\\/Hs\\/3dZxXk.RkeO0xH3jrWr7mUPWyuIB\\/IDnTDY1iowvHC"}]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:CuratorMedia","View:CuratorMedia","Create:CuratorMedia","Update:CuratorMedia","Delete:CuratorMedia","Restore:CuratorMedia","ForceDelete:CuratorMedia","ForceDeleteAny:CuratorMedia","RestoreAny:CuratorMedia","Replicate:CuratorMedia","Reorder:CuratorMedia","ViewOwn:CuratorMedia","UpdateOwn:CuratorMedia","DeleteOwn:CuratorMedia","RestoreOwn:CuratorMedia","ForceDeleteOwn:CuratorMedia","DeleteUsed:CuratorMedia","ForceDeleteUsed:CuratorMedia","ViewAny:Activity","View:Activity","Create:Activity","Update:Activity","Delete:Activity","Restore:Activity","ForceDelete:Activity","ForceDeleteAny:Activity","RestoreAny:Activity","Replicate:Activity","Reorder:Activity","ViewAny:Post","View:Post","Create:Post","Update:Post","Delete:Post","Restore:Post","ForceDelete:Post","ForceDeleteAny:Post","RestoreAny:Post","Replicate:Post","Reorder:Post","ViewOwn:Post","UpdateOwn:Post","DeleteOwn:Post","RestoreOwn:Post","ForceDeleteOwn:Post","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","View:StarterKitInfoWidget"]},{"name":"panel_user","guard_name":"web","permissions":[]},{"name":"admin","guard_name":"web","permissions":["ViewAny:Post","View:Post","Create:Post","Update:Post","Delete:Post","Restore:Post","ForceDelete:Post","ForceDeleteAny:Post","RestoreAny:Post","Replicate:Post","Reorder:Post","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:CuratorMedia","View:CuratorMedia","Create:CuratorMedia","Update:CuratorMedia","Delete:CuratorMedia","Restore:CuratorMedia"]},{"name":"member","guard_name":"web","permissions":["ViewAny:CuratorMedia","ViewOwn:CuratorMedia","UpdateOwn:CuratorMedia","DeleteOwn:CuratorMedia","RestoreOwn:CuratorMedia","ViewAny:Post","ViewOwn:Post","UpdateOwn:Post","DeleteOwn:Post","RestoreOwn:Post"]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
