<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Cache ni tozalash
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissionlar ro'yxati
        $permissions = [
            // Branches
            'view branches',
            'manage branches',

            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Categories
            'view categories',
            'manage categories',

            // Shifts
            'open shift',
            'close shift',
            'confirm handover',
            'view all shifts',

            // Sales
            'create sale',
            'view sales',
            'view all sales',

            // Stock
            'view stock',
            'update stock',
            'transfer stock',

            // Finance
            'reconcile payments',
            'view reports',
            'view all reports',

            // Expenses
            'create expense',
            'view expenses',
            'manage expenses',

            // Debts
            'create debt',
            'manage debts',

            // KPI
            'view own kpi',
            'manage kpi settings',

            // Inventory
            'conduct inventory',
            'manage inventory',

            // Schedules
            'view own schedule',
            'manage schedules',

            // Users
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // === ADMIN roli — barcha permissionlar ===
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // === MANAGER roli ===
        $managerPermissions = [
            'view branches',
            'view products',
            'create products',
            'edit products',
            'view categories',
            'open shift',
            'close shift',
            'confirm handover',
            'view all shifts',
            'create sale',
            'view sales',
            'view all sales',
            'view stock',
            'update stock',
            'transfer stock',
            'reconcile payments',
            'view reports',
            'create expense',
            'view expenses',
            'create debt',
            'manage debts',
            'view own kpi',
            'manage kpi settings',
            'conduct inventory',
            'manage inventory',
            'view own schedule',
            'manage schedules',
        ];

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions($managerPermissions);

        // === EMPLOYEE roli ===
        $employeePermissions = [
            'view branches',
            'view products',
            'view categories',
            'open shift',
            'close shift',
            'confirm handover',
            'create sale',
            'view sales',
            'view stock',
            'create expense',
            'create debt',
            'view own kpi',
            'conduct inventory',
            'view own schedule',
        ];

        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions($employeePermissions);

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions count'],
            [
                ['admin', $admin->permissions()->count()],
                ['manager', $manager->permissions()->count()],
                ['employee', $employee->permissions()->count()],
            ]
        );
    }
}
