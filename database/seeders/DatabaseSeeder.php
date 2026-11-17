<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Sample data may only be seeded in local or testing environments.');
        }

        $this->call([
            RoleAndPermissionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            ManagementDemoSeeder::class,
        ]);
    }
}
