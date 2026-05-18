<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $categories = InventoryCategory::all()->keyBy('slug');
        $supplierId = Supplier::query()->inRandomOrder('')->value('id');
        $locations = ['Main Warehouse', 'Tool Room Shelf A', 'Tool Room Shelf B', 'Service Bay', 'Delivery Rack', 'Storage Shelf 3'];

        $itemsByCategory = [
            'tools' => [
                ['name' => 'Adjustable Wrench', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 45],
                ['name' => 'Ball-peen Hammer', 'unit' => 'pcs', 'price_min' => 15, 'price_max' => 35],
                ['name' => 'Bolt Cutter 24"', 'unit' => 'pcs', 'price_min' => 55, 'price_max' => 95],
                ['name' => 'Bow Saw 21"', 'unit' => 'pcs', 'price_min' => 25, 'price_max' => 50],
                ['name' => 'Chisel Set 4pc', 'unit' => 'set', 'price_min' => 22, 'price_max' => 48],
                ['name' => 'Clamps Set 6pc', 'unit' => 'set', 'price_min' => 35, 'price_max' => 70],
                ['name' => 'Crowbar 36"', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 55],
                ['name' => 'Hacksaw Frame', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 45],
                ['name' => 'Hand Saw 22"', 'unit' => 'pcs', 'price_min' => 20, 'price_max' => 42],
                ['name' => 'Socket Wrench Set 1/2"', 'unit' => 'set', 'price_min' => 68, 'price_max' => 145],
            ],
            'materials' => [
                ['name' => 'Portland Cement 50kg', 'unit' => 'bag', 'price_min' => 6, 'price_max' => 12],
                ['name' => 'Ready Mix Concrete 25kg', 'unit' => 'bag', 'price_min' => 14, 'price_max' => 24],
                ['name' => 'Gravel 20mm', 'unit' => 'bag', 'price_min' => 3, 'price_max' => 7],
                ['name' => 'Sand Fine', 'unit' => 'bag', 'price_min' => 3, 'price_max' => 8],
                ['name' => 'Plywood 4x8', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 55],
                ['name' => 'Marine Plywood 4x8', 'unit' => 'pcs', 'price_min' => 55, 'price_max' => 98],
                ['name' => 'GI Sheet 0.5mm', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 48],
                ['name' => 'Hollow Block 6"', 'unit' => 'pcs', 'price_min' => 0.85, 'price_max' => 1.50],
                ['name' => 'Ceramic Tile 300x300mm', 'unit' => 'box', 'price_min' => 12, 'price_max' => 25],
                ['name' => 'Epoxy Primer 4L', 'unit' => 'liter', 'price_min' => 48, 'price_max' => 92],
            ],
            'electrical' => [
                ['name' => 'Circuit Breaker 20A', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 26],
                ['name' => 'Electrical Tape 3/4"', 'unit' => 'roll', 'price_min' => 2, 'price_max' => 5],
                ['name' => 'Wire Stripper', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 38],
                ['name' => 'Digital Multimeter', 'unit' => 'pcs', 'price_min' => 58, 'price_max' => 135],
                ['name' => 'Voltage Tester', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 48],
                ['name' => 'Extension Cord 25ft', 'unit' => 'pcs', 'price_min' => 24, 'price_max' => 52],
                ['name' => 'Duplex Outlet', 'unit' => 'pcs', 'price_min' => 4, 'price_max' => 10],
                ['name' => 'LED Bulb 9W', 'unit' => 'pcs', 'price_min' => 3, 'price_max' => 8],
                ['name' => 'Panel Board 12-circuit', 'unit' => 'pcs', 'price_min' => 120, 'price_max' => 260],
                ['name' => 'Conduit Pipe 3/4"', 'unit' => 'meter', 'price_min' => 1.80, 'price_max' => 3.50],
            ],
            'plumbing' => [
                ['name' => 'Basin Wrench', 'unit' => 'pcs', 'price_min' => 16, 'price_max' => 38],
                ['name' => 'Pipe Cutter 1-2"', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42],
                ['name' => 'Pipe Wrench 14"', 'unit' => 'pcs', 'price_min' => 26, 'price_max' => 55],
                ['name' => 'Plunger Cup', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 16],
                ['name' => 'PVC Elbow 90° 1"', 'unit' => 'pcs', 'price_min' => 0.65, 'price_max' => 1.30],
                ['name' => 'PVC Tee 1"', 'unit' => 'pcs', 'price_min' => 0.80, 'price_max' => 1.55],
                ['name' => 'Ball Valve 1/2"', 'unit' => 'pcs', 'price_min' => 7, 'price_max' => 15],
                ['name' => 'Gate Valve 1"', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 26],
                ['name' => 'Faucet Single Handle', 'unit' => 'pcs', 'price_min' => 45, 'price_max' => 95],
                ['name' => 'Thread Seal Tape', 'unit' => 'roll', 'price_min' => 2, 'price_max' => 5],
            ],
            'safety-equipment' => [
                ['name' => 'Hard Hat ANSI', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42],
                ['name' => 'Safety Glasses Clear', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 18],
                ['name' => 'Safety Harness Full-body', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 195],
                ['name' => 'Steel-toe Boots', 'unit' => 'pair', 'price_min' => 78, 'price_max' => 165],
                ['name' => 'Leather Work Gloves', 'unit' => 'pair', 'price_min' => 12, 'price_max' => 28],
                ['name' => 'NIOSH Respirator', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 62],
                ['name' => 'Face Shield', 'unit' => 'pcs', 'price_min' => 24, 'price_max' => 48],
                ['name' => 'Dust Mask N95', 'unit' => 'box', 'price_min' => 18, 'price_max' => 42],
                ['name' => 'Fire Extinguisher ABC 5lb', 'unit' => 'pcs', 'price_min' => 48, 'price_max' => 95],
                ['name' => 'First Aid Kit 50pc', 'unit' => 'box', 'price_min' => 34, 'price_max' => 68],
            ],
        ];

        $categorySlug = fake()->randomElement(array_keys($itemsByCategory));
        $category = $categories->get($categorySlug) ?? $categories->first();
        $item = fake()->randomElement($itemsByCategory[$categorySlug]);

        return [
            'item_code' => 'RGV-' . strtoupper(fake()->unique()->bothify('???###')),
            'name' => $item['name'],
            'description' => fake()->sentence(12),
            'category_id' => $category->id,
            'supplier_id' => $supplierId,
            'quantity' => fake()->numberBetween(8, 120),
            'unit' => $item['unit'],
            'unit_cost' => fake()->randomFloat(2, $item['price_min'], $item['price_max']),
            'status' => fake()->randomElement(['available', 'available', 'borrowed', 'maintenance']),
            'condition' => fake()->randomElement(['new', 'good', 'fair']),
            'location' => fake()->randomElement($locations),
            'low_stock_threshold' => fake()->numberBetween(5, 15),
            'date_added' => fake()->dateTimeBetween('-18 months', 'now'),
            'is_active' => true,
        ];
    }
}
