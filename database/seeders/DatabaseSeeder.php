<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            BookingSeeder::class,
            BorrowRequestSeeder::class,
        ]);

        if (env('MASS_BOOK_SEED', false)) {
            $this->call(MassBookSeeder::class);
        }
    }
}
