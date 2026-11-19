<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Booking permissions
            'view bookings',
            'create bookings',
            'edit bookings',
            'delete bookings',
            'approve bookings',
            'reject bookings',
            'export bookings',

            // Inventory permissions
            'view inventories',
            'create inventories',
            'edit inventories',
            'delete inventories',
            'manage inventory stock',
            'export inventories',

            // Borrow request permissions
            'view borrow requests',
            'create borrow requests',
            'approve borrow requests',
            'reject borrow requests',
            'return borrowed items',

            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',

            // Report permissions
            'view reports',
            'generate reports',
            'export reports',
            'ai analytics',

            // System permissions
            'view activity logs',
            'manage settings',
            'manage backups',
            'manage imports',
            'manage exports',
            'view audit logs',
            'manage api security',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employeeRole->givePermissionTo([
            'view bookings',
            'view inventories',
            'create borrow requests',
            'view borrow requests',
            'return borrowed items',
        ]);
    }
}
