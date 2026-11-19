<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryCategory;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tools',
                'slug' => 'tools',
                'description' => 'Hand tools and power tools used for construction, maintenance and industrial work.',
                'icon' => 'wrench',
                'is_active' => true,
            ],
            [
                'name' => 'Materials',
                'slug' => 'materials',
                'description' => 'Construction materials and consumables for building, finishing and repairs.',
                'icon' => 'boxes-stacked',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'description' => 'Electrical supplies, cabling and devices for installation and service work.',
                'icon' => 'bolt',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'description' => 'Plumbing fixtures, fittings and repair tools for water systems.',
                'icon' => 'faucet',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Equipment',
                'slug' => 'safety-equipment',
                'description' => 'Personal protective equipment and safety gear for job sites.',
                'icon' => 'shield-check',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            InventoryCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
