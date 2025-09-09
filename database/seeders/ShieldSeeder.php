<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure roles exist (guard: web)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $panelUserRole = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        // Create users and assign roles
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin User', 'password' => 'password']
        );
        $superAdmin->syncRoles([$superAdminRole, $panelUserRole]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => 'password']
        );
        $admin->syncRoles([$adminRole, $panelUserRole]);

        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            ['name' => 'Member User', 'password' => 'password']
        );
        $member->syncRoles([$memberRole, $panelUserRole]);
    }
}
