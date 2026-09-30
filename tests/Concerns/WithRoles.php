<?php

namespace Tests\Concerns;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait WithRoles
{
    protected function createEmployee(array $attributes = []): User
    {
        $this->ensureRoles();

        $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
        $user->assignRole('employee');

        return $user;
    }

    protected function createAdmin(array $attributes = []): User
    {
        $this->ensureRoles();

        $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
        $user->assignRole('admin');

        return $user;
    }

    protected function ensureRoles(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('employee', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createInventory(array $attributes = []): Inventory
    {
        InventoryCategory::firstOrCreate(['slug' => 'tools'], ['name' => 'Tools', 'is_active' => true]);

        return Inventory::factory()->create(array_merge([
            'status' => 'available',
            'is_active' => true,
        ], $attributes));
    }
}
