<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = InventoryCategory::query()
            ->whereIn('slug', ['tools', 'materials'])
            ->get()
            ->keyBy('slug');

        $items = [
            // [item_code, name, brand, category, quantity, unit]
            ['RGV-001', 'Impact Wrench', 'INGCO', 'tools', 3, 'unit'],
            ['RGV-002', 'Tile Cutter', 'INGCO', 'tools', 1, 'unit'],
            ['RGV-003', 'Cordless Drill', 'INGCO', 'tools', 2, 'unit'],
            ['RGV-004', 'Cordless Tile Vibration Machine', 'INGCO', 'tools', 2, 'unit'],
            ['RGV-005', 'Concrete Vibrator', null, 'tools', 2, 'unit'],
            ['RGV-006', 'Plate Compactor', null, 'tools', 1, 'unit'],
            ['RGV-007', 'Rebar 16mm G.40', null, 'materials', 200, 'pcs'],
            ['RGV-008', 'Rebar 10mm G.40', null, 'materials', 300, 'pcs'],
            ['RGV-009', 'Handheld Jackhammer', 'INGCO', 'tools', 1, 'unit'],
            ['RGV-010', 'Jackhammer', 'Makita', 'tools', 1, 'unit'],
            ['RGV-011', 'Round Shovel', null, 'tools', 5, 'pcs'],
            ['RGV-012', 'Manifold Gauge', null, 'tools', 3, 'unit'],
        ];

        foreach ($items as [$itemCode, $name, $brand, $categorySlug, $quantity, $unit]) {
            if (! $categories->has($categorySlug)) {
                continue;
            }

            Inventory::updateOrCreate(
                ['item_code' => $itemCode],
                [
                    'item_code' => $itemCode,
                    'name' => $name,
                    'brand' => $brand,
                    'description' => $this->buildDescription($name, $brand),
                    'category_id' => $categories->get($categorySlug)->id,
                    'supplier_id' => null,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_cost' => null,
                    'status' => 'available',
                    'condition' => 'good',
                    'location' => null,
                    'low_stock_threshold' => 1,
                    'date_added' => now()->toDateString(),
                    'is_active' => true,
                ]
            );
        }
    }

    private function buildDescription(string $name, ?string $brand): ?string
    {
        if ($brand === null) {
            return null;
        }

        return sprintf('%s %s', $brand, $name);
    }
}
