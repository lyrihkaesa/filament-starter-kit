<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

final class ShieldSeeder extends Seeder
{
    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::query()->create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }

    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[{"id":"019d25e9-1e6f-71cf-b3e5-53f37b334dfd","name":"Super Admin User","email":"superadmin@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:07.000000Z","updated_at":"2026-03-25T16:52:07.000000Z","deleted_at":null,"anonymized_at":null,"password":"$2y$12$2cGoCTu\\/Hs\\/3dZxXk.RkeO0xH3jrWr7mUPWyuIB\\/IDnTDY1iowvHC","roles":["super_admin","panel_user"],"permissions":[]},{"id":"019d25e9-1f96-708b-aad1-8a7b3cbeb1d1","name":"Admin User","email":"admin@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:08.000000Z","updated_at":"2026-03-25T16:52:08.000000Z","deleted_at":null,"anonymized_at":null,"password":"$2y$12$h7dlYkXJ8qLlucs9jHWCjOVsEa0liNPyVTPfyF9.iQ5zQS9BPppxa","roles":["admin","panel_user"],"permissions":[]},{"id":"019d25e9-20b3-701f-b8b0-3aca35664121","name":"Member User","email":"member@example.com","email_verified_at":null,"created_at":"2026-03-25T16:52:08.000000Z","updated_at":"2026-03-25T16:52:08.000000Z","deleted_at":null,"anonymized_at":null,"password":"$2y$12$EHYU\\/ORYCKOsFesotFF\\/NOp7byazG..fGGG3CjCoZ\\/Srh2GMLSs6u","roles":["member","panel_user"],"permissions":[]}]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Post","View:Post","Create:Post","Update:Post","Delete:Post","Restore:Post","ForceDelete:Post","ForceDeleteAny:Post","RestoreAny:Post","Replicate:Post","Reorder:Post","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","View:StarterKitInfoWidget"]},{"name":"panel_user","guard_name":"web","permissions":[]},{"name":"admin","guard_name":"web","permissions":["ViewAny:Post","View:Post","Create:Post","Update:Post","Delete:Post","Restore:Post","ForceDelete:Post","ForceDeleteAny:Post","RestoreAny:Post","Replicate:Post","Reorder:Post","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User"]},{"name":"member","guard_name":"web","permissions":[]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (filled($tenants) && $tenants !== '[]') {
            $this->seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        $this->makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        self::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (filled($users) && $users !== '[]') {
            $this->seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (filled($userTenantPivot) && $userTenantPivot !== '[]') {
            $this->seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    private function seedTenants(string $tenants): void
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

    private function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = User::class;
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::query()->firstOrCreate(['email' => $data['email']], $data);

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

    private function seedUserTenantPivot(string $pivot): void
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
            if (filled($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if ($uniqueKeys !== []) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    private function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var Model $permissionModel */
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
            if ($tenancyEnabled && filled($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::query()->firstOrCreate($roleData);

            if (filled($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::query()->firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }
}
