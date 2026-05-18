<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = InventoryCategory::query()->whereIn('slug', [
            'tools',
            'materials',
            'electrical',
            'plumbing',
            'safety-equipment',
        ], 'and', false)->get()->keyBy('slug');

        $supplierIds = Supplier::query()->pluck('id', null)->all();
        $faker = fake();
        $locations = [
            'Main Warehouse',
            'Tool Room Shelf A',
            'Tool Room Shelf B',
            'Service Bay',
            'Delivery Rack',
            'Storage Shelf 3',
        ];

        $items = [
            'tools' => [
                ['name' => 'Adjustable Wrench', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 45, 'stock_min' => 12, 'stock_max' => 40, 'condition' => 'new'],
                ['name' => 'Ball-peen Hammer', 'unit' => 'pcs', 'price_min' => 15, 'price_max' => 35, 'stock_min' => 10, 'stock_max' => 30, 'condition' => 'new'],
                ['name' => 'Bolt Cutter 24"', 'unit' => 'pcs', 'price_min' => 55, 'price_max' => 95, 'stock_min' => 6, 'stock_max' => 18, 'condition' => 'good'],
                ['name' => 'Bow Saw 21"', 'unit' => 'pcs', 'price_min' => 25, 'price_max' => 50, 'stock_min' => 8, 'stock_max' => 22, 'condition' => 'new'],
                ['name' => 'Chisel Set 4pc', 'unit' => 'set', 'price_min' => 22, 'price_max' => 48, 'stock_min' => 10, 'stock_max' => 28, 'condition' => 'new'],
                ['name' => 'Clamps Set 6pc', 'unit' => 'set', 'price_min' => 35, 'price_max' => 70, 'stock_min' => 8, 'stock_max' => 24, 'condition' => 'new'],
                ['name' => 'Crowbar 36"', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 55, 'stock_min' => 6, 'stock_max' => 20, 'condition' => 'good'],
                ['name' => 'File Set 5pc', 'unit' => 'set', 'price_min' => 18, 'price_max' => 38, 'stock_min' => 12, 'stock_max' => 26, 'condition' => 'new'],
                ['name' => 'Hacksaw Frame', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 45, 'stock_min' => 12, 'stock_max' => 34, 'condition' => 'new'],
                ['name' => 'Hand Saw 22"', 'unit' => 'pcs', 'price_min' => 20, 'price_max' => 42, 'stock_min' => 10, 'stock_max' => 30, 'condition' => 'new'],
                ['name' => 'Rubber Mallet', 'unit' => 'pcs', 'price_min' => 16, 'price_max' => 32, 'stock_min' => 14, 'stock_max' => 40, 'condition' => 'new'],
                ['name' => 'Multi-tool Pliers', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 58, 'stock_min' => 10, 'stock_max' => 26, 'condition' => 'new'],
                ['name' => 'Needle-nose Pliers', 'unit' => 'pcs', 'price_min' => 14, 'price_max' => 30, 'stock_min' => 14, 'stock_max' => 38, 'condition' => 'new'],
                ['name' => 'Screwdriver Set 20pc', 'unit' => 'set', 'price_min' => 32, 'price_max' => 68, 'stock_min' => 10, 'stock_max' => 24, 'condition' => 'new'],
                ['name' => 'Shovel Round Point', 'unit' => 'pcs', 'price_min' => 26, 'price_max' => 55, 'stock_min' => 8, 'stock_max' => 22, 'condition' => 'good'],
                ['name' => 'Sledgehammer 10lb', 'unit' => 'pcs', 'price_min' => 42, 'price_max' => 85, 'stock_min' => 6, 'stock_max' => 16, 'condition' => 'good'],
                ['name' => 'Socket Wrench Set 1/2"', 'unit' => 'set', 'price_min' => 68, 'price_max' => 145, 'stock_min' => 8, 'stock_max' => 20, 'condition' => 'new'],
                ['name' => 'Tape Measure 25ft', 'unit' => 'pcs', 'price_min' => 11, 'price_max' => 22, 'stock_min' => 16, 'stock_max' => 48, 'condition' => 'new'],
                ['name' => 'Torque Wrench 1/2"', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 210, 'stock_min' => 6, 'stock_max' => 18, 'condition' => 'new'],
                ['name' => 'Utility Knife', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 18, 'stock_min' => 18, 'stock_max' => 50, 'condition' => 'new'],
                ['name' => 'Vise Grips Locking Pliers', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 48, 'stock_min' => 12, 'stock_max' => 28, 'condition' => 'new'],
                ['name' => 'Wheelbarrow Steel Tray', 'unit' => 'pcs', 'price_min' => 110, 'price_max' => 220, 'stock_min' => 4, 'stock_max' => 14, 'condition' => 'good'],
                ['name' => 'Cordless Drill 18V', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 210, 'stock_min' => 8, 'stock_max' => 22, 'condition' => 'new'],
                ['name' => 'Angle Grinder 4-1/2"', 'unit' => 'pcs', 'price_min' => 75, 'price_max' => 160, 'stock_min' => 8, 'stock_max' => 20, 'condition' => 'new'],
                ['name' => 'Chainsaw 16"', 'unit' => 'pcs', 'price_min' => 185, 'price_max' => 320, 'stock_min' => 5, 'stock_max' => 12, 'condition' => 'good'],
                ['name' => 'Circular Saw 7-1/4"', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 225, 'stock_min' => 7, 'stock_max' => 18, 'condition' => 'new'],
                ['name' => 'Drill Press 12"', 'unit' => 'pcs', 'price_min' => 420, 'price_max' => 780, 'stock_min' => 3, 'stock_max' => 8, 'condition' => 'good'],
                ['name' => 'Hammer Drill 1/2"', 'unit' => 'pcs', 'price_min' => 110, 'price_max' => 240, 'stock_min' => 6, 'stock_max' => 14, 'condition' => 'new'],
                ['name' => 'Impact Driver 18V', 'unit' => 'pcs', 'price_min' => 120, 'price_max' => 250, 'stock_min' => 8, 'stock_max' => 18, 'condition' => 'new'],
                ['name' => 'Jigsaw 5A', 'unit' => 'pcs', 'price_min' => 65, 'price_max' => 135, 'stock_min' => 8, 'stock_max' => 20, 'condition' => 'new'],
                ['name' => 'Miter Saw 10"', 'unit' => 'pcs', 'price_min' => 290, 'price_max' => 560, 'stock_min' => 4, 'stock_max' => 10, 'condition' => 'good'],
                ['name' => 'Nail Gun 16ga', 'unit' => 'pcs', 'price_min' => 155, 'price_max' => 320, 'stock_min' => 6, 'stock_max' => 14, 'condition' => 'new'],
                ['name' => 'Rotary Tool Kit', 'unit' => 'set', 'price_min' => 55, 'price_max' => 110, 'stock_min' => 10, 'stock_max' => 24, 'condition' => 'new'],
                ['name' => 'Orbital Sander', 'unit' => 'pcs', 'price_min' => 42, 'price_max' => 95, 'stock_min' => 10, 'stock_max' => 22, 'condition' => 'new'],
                ['name' => 'Table Saw 10"', 'unit' => 'pcs', 'price_min' => 520, 'price_max' => 980, 'stock_min' => 2, 'stock_max' => 6, 'condition' => 'good'],
                ['name' => 'Wood Router 2-1/4HP', 'unit' => 'pcs', 'price_min' => 145, 'price_max' => 320, 'stock_min' => 6, 'stock_max' => 14, 'condition' => 'new'],
            ],
            'materials' => [
                ['name' => 'Portland Cement 50kg', 'unit' => 'bag', 'price_min' => 6, 'price_max' => 12, 'stock_min' => 40, 'stock_max' => 120, 'condition' => 'new'],
                ['name' => 'Ready Mix Concrete 25kg', 'unit' => 'bag', 'price_min' => 14, 'price_max' => 24, 'stock_min' => 30, 'stock_max' => 80, 'condition' => 'new'],
                ['name' => 'Gravel 20mm', 'unit' => 'bag', 'price_min' => 3, 'price_max' => 7, 'stock_min' => 80, 'stock_max' => 180, 'condition' => 'new'],
                ['name' => 'Sand Fine', 'unit' => 'bag', 'price_min' => 3, 'price_max' => 8, 'stock_min' => 70, 'stock_max' => 170, 'condition' => 'new'],
                ['name' => 'Rebar 10mm', 'unit' => 'meter', 'price_min' => 1, 'price_max' => 3, 'stock_min' => 120, 'stock_max' => 320, 'condition' => 'new'],
                ['name' => 'Plywood 4x8', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 55, 'stock_min' => 20, 'stock_max' => 60, 'condition' => 'new'],
                ['name' => 'Marine Plywood 4x8', 'unit' => 'pcs', 'price_min' => 55, 'price_max' => 98, 'stock_min' => 12, 'stock_max' => 28, 'condition' => 'new'],
                ['name' => 'Coco Lumber 2x4', 'unit' => 'meter', 'price_min' => 4, 'price_max' => 9, 'stock_min' => 60, 'stock_max' => 160, 'condition' => 'new'],
                ['name' => 'GI Sheet 0.5mm', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 48, 'stock_min' => 24, 'stock_max' => 72, 'condition' => 'new'],
                ['name' => 'Steel Bar 12mm', 'unit' => 'meter', 'price_min' => 2, 'price_max' => 5, 'stock_min' => 90, 'stock_max' => 220, 'condition' => 'new'],
                ['name' => 'Hollow Block 6"', 'unit' => 'pcs', 'price_min' => 0.85, 'price_max' => 1.50, 'stock_min' => 240, 'stock_max' => 520, 'condition' => 'new'],
                ['name' => 'Hollow Block 8"', 'unit' => 'pcs', 'price_min' => 1.05, 'price_max' => 1.80, 'stock_min' => 180, 'stock_max' => 420, 'condition' => 'new'],
                ['name' => 'Ceramic Tile 300x300mm', 'unit' => 'box', 'price_min' => 12, 'price_max' => 25, 'stock_min' => 30, 'stock_max' => 90, 'condition' => 'new'],
                ['name' => 'Ceramic Tile 600x600mm', 'unit' => 'box', 'price_min' => 28, 'price_max' => 52, 'stock_min' => 18, 'stock_max' => 48, 'condition' => 'new'],
                ['name' => 'PVC Pipe 1" 3m', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 22, 'stock_min' => 40, 'stock_max' => 110, 'condition' => 'new'],
                ['name' => 'PVC Pipe 2" 3m', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 36, 'stock_min' => 28, 'stock_max' => 78, 'condition' => 'new'],
                ['name' => 'Roofing Nails 50mm', 'unit' => 'box', 'price_min' => 9, 'price_max' => 18, 'stock_min' => 24, 'stock_max' => 70, 'condition' => 'new'],
                ['name' => 'Welding Rod E6013', 'unit' => 'box', 'price_min' => 18, 'price_max' => 34, 'stock_min' => 22, 'stock_max' => 52, 'condition' => 'new'],
                ['name' => 'Paint Thinner 4L', 'unit' => 'liter', 'price_min' => 18, 'price_max' => 32, 'stock_min' => 18, 'stock_max' => 45, 'condition' => 'new'],
                ['name' => 'Epoxy Primer 4L', 'unit' => 'liter', 'price_min' => 48, 'price_max' => 92, 'stock_min' => 14, 'stock_max' => 32, 'condition' => 'new'],
            ],
            'electrical' => [
                ['name' => 'Circuit Breaker 20A', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 26, 'stock_min' => 30, 'stock_max' => 80, 'condition' => 'new'],
                ['name' => 'Electrical Tape 3/4"', 'unit' => 'roll', 'price_min' => 2, 'price_max' => 5, 'stock_min' => 60, 'stock_max' => 160, 'condition' => 'new'],
                ['name' => 'Wire Stripper', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 38, 'stock_min' => 20, 'stock_max' => 60, 'condition' => 'new'],
                ['name' => 'Digital Multimeter', 'unit' => 'pcs', 'price_min' => 58, 'price_max' => 135, 'stock_min' => 12, 'stock_max' => 34, 'condition' => 'new'],
                ['name' => 'Voltage Tester', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 48, 'stock_min' => 20, 'stock_max' => 56, 'condition' => 'new'],
                ['name' => 'Extension Cord 25ft', 'unit' => 'pcs', 'price_min' => 24, 'price_max' => 52, 'stock_min' => 18, 'stock_max' => 50, 'condition' => 'new'],
                ['name' => 'Junction Box 4"', 'unit' => 'pcs', 'price_min' => 6, 'price_max' => 14, 'stock_min' => 40, 'stock_max' => 112, 'condition' => 'new'],
                ['name' => 'Electrical Wire 14 AWG 100ft', 'unit' => 'roll', 'price_min' => 42, 'price_max' => 78, 'stock_min' => 22, 'stock_max' => 58, 'condition' => 'new'],
                ['name' => 'Duplex Outlet', 'unit' => 'pcs', 'price_min' => 4, 'price_max' => 10, 'stock_min' => 50, 'stock_max' => 140, 'condition' => 'new'],
                ['name' => 'LED Bulb 9W', 'unit' => 'pcs', 'price_min' => 3, 'price_max' => 8, 'stock_min' => 80, 'stock_max' => 220, 'condition' => 'new'],
                ['name' => 'Panel Board 12-circuit', 'unit' => 'pcs', 'price_min' => 120, 'price_max' => 260, 'stock_min' => 6, 'stock_max' => 16, 'condition' => 'new'],
                ['name' => 'Conduit Pipe 3/4"', 'unit' => 'meter', 'price_min' => 1.80, 'price_max' => 3.50, 'stock_min' => 120, 'stock_max' => 340, 'condition' => 'new'],
                ['name' => 'Cable Cutter', 'unit' => 'pcs', 'price_min' => 26, 'price_max' => 58, 'stock_min' => 16, 'stock_max' => 44, 'condition' => 'new'],
                ['name' => 'Crimping Tool', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 64, 'stock_min' => 16, 'stock_max' => 42, 'condition' => 'new'],
                ['name' => 'Grounding Rod 8ft', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 24, 'stock_min' => 28, 'stock_max' => 74, 'condition' => 'new'],
                ['name' => 'Smoke Detector', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 20, 'stock_max' => 58, 'condition' => 'new'],
                ['name' => 'GFCI Outlet', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 28, 'stock_min' => 20, 'stock_max' => 58, 'condition' => 'new'],
                ['name' => 'Power Strip 6-Outlet', 'unit' => 'pcs', 'price_min' => 14, 'price_max' => 32, 'stock_min' => 30, 'stock_max' => 88, 'condition' => 'new'],
                ['name' => 'Smart Light Switch', 'unit' => 'pcs', 'price_min' => 24, 'price_max' => 52, 'stock_min' => 18, 'stock_max' => 50, 'condition' => 'new'],
                ['name' => 'Cable Tray Section', 'unit' => 'meter', 'price_min' => 8, 'price_max' => 18, 'stock_min' => 30, 'stock_max' => 92, 'condition' => 'new'],
            ],
            'plumbing' => [
                ['name' => 'Basin Wrench', 'unit' => 'pcs', 'price_min' => 16, 'price_max' => 38, 'stock_min' => 20, 'stock_max' => 52, 'condition' => 'new'],
                ['name' => 'Pipe Cutter 1-2"', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 18, 'stock_max' => 44, 'condition' => 'new'],
                ['name' => 'Pipe Wrench 14"', 'unit' => 'pcs', 'price_min' => 26, 'price_max' => 55, 'stock_min' => 14, 'stock_max' => 38, 'condition' => 'good'],
                ['name' => 'Plunger Cup', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 16, 'stock_min' => 32, 'stock_max' => 90, 'condition' => 'new'],
                ['name' => 'PVC Elbow 90° 1"', 'unit' => 'pcs', 'price_min' => 0.65, 'price_max' => 1.30, 'stock_min' => 120, 'stock_max' => 320, 'condition' => 'new'],
                ['name' => 'PVC Tee 1"', 'unit' => 'pcs', 'price_min' => 0.80, 'price_max' => 1.55, 'stock_min' => 110, 'stock_max' => 280, 'condition' => 'new'],
                ['name' => 'Ball Valve 1/2"', 'unit' => 'pcs', 'price_min' => 7, 'price_max' => 15, 'stock_min' => 40, 'stock_max' => 110, 'condition' => 'new'],
                ['name' => 'Gate Valve 1"', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 26, 'stock_min' => 30, 'stock_max' => 84, 'condition' => 'new'],
                ['name' => 'Faucet Single Handle', 'unit' => 'pcs', 'price_min' => 45, 'price_max' => 95, 'stock_min' => 18, 'stock_max' => 48, 'condition' => 'new'],
                ['name' => 'Shower Head Rain', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 58, 'stock_min' => 20, 'stock_max' => 52, 'condition' => 'new'],
                ['name' => 'Toilet Bowl 2-piece', 'unit' => 'pcs', 'price_min' => 185, 'price_max' => 420, 'stock_min' => 8, 'stock_max' => 22, 'condition' => 'new'],
                ['name' => 'Sink Drain Assembly', 'unit' => 'pcs', 'price_min' => 14, 'price_max' => 32, 'stock_min' => 28, 'stock_max' => 72, 'condition' => 'new'],
                ['name' => 'Water Hose 25ft', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 22, 'stock_max' => 60, 'condition' => 'new'],
                ['name' => 'Thread Seal Tape', 'unit' => 'roll', 'price_min' => 2, 'price_max' => 5, 'stock_min' => 80, 'stock_max' => 200, 'condition' => 'new'],
                ['name' => 'Flexible Connector 3/8"', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 18, 'stock_min' => 30, 'stock_max' => 84, 'condition' => 'new'],
                ['name' => 'PVC Pipe 1" 3m', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 24, 'stock_min' => 40, 'stock_max' => 110, 'condition' => 'new'],
                ['name' => 'PEX Pipe 1/2" 10m', 'unit' => 'meter', 'price_min' => 1.60, 'price_max' => 3.25, 'stock_min' => 70, 'stock_max' => 190, 'condition' => 'new'],
                ['name' => 'Pipe Insulation 2m', 'unit' => 'meter', 'price_min' => 2.10, 'price_max' => 4.60, 'stock_min' => 60, 'stock_max' => 150, 'condition' => 'new'],
                ['name' => 'Copper Compression Fitting', 'unit' => 'pcs', 'price_min' => 0.90, 'price_max' => 2.20, 'stock_min' => 90, 'stock_max' => 240, 'condition' => 'new'],
            ],
            'safety-equipment' => [
                ['name' => 'Hard Hat ANSI', 'unit' => 'pcs', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 30, 'stock_max' => 90, 'condition' => 'new'],
                ['name' => 'Safety Glasses Clear', 'unit' => 'pcs', 'price_min' => 8, 'price_max' => 18, 'stock_min' => 50, 'stock_max' => 140, 'condition' => 'new'],
                ['name' => 'Safety Harness Full-body', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 195, 'stock_min' => 10, 'stock_max' => 28, 'condition' => 'new'],
                ['name' => 'Steel-toe Boots', 'unit' => 'pair', 'price_min' => 78, 'price_max' => 165, 'stock_min' => 18, 'stock_max' => 46, 'condition' => 'new'],
                ['name' => 'Leather Work Gloves', 'unit' => 'pair', 'price_min' => 12, 'price_max' => 28, 'stock_min' => 40, 'stock_max' => 110, 'condition' => 'new'],
                ['name' => 'NIOSH Respirator', 'unit' => 'pcs', 'price_min' => 28, 'price_max' => 62, 'stock_min' => 20, 'stock_max' => 60, 'condition' => 'new'],
                ['name' => 'Face Shield', 'unit' => 'pcs', 'price_min' => 24, 'price_max' => 48, 'stock_min' => 22, 'stock_max' => 64, 'condition' => 'new'],
                ['name' => 'Ear Protection Muffs', 'unit' => 'pcs', 'price_min' => 22, 'price_max' => 55, 'stock_min' => 18, 'stock_max' => 50, 'condition' => 'new'],
                ['name' => 'Dust Mask N95', 'unit' => 'box', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 20, 'stock_max' => 80, 'condition' => 'new'],
                ['name' => 'Fire Extinguisher ABC 5lb', 'unit' => 'pcs', 'price_min' => 48, 'price_max' => 95, 'stock_min' => 8, 'stock_max' => 24, 'condition' => 'new'],
                ['name' => 'First Aid Kit 50pc', 'unit' => 'box', 'price_min' => 34, 'price_max' => 68, 'stock_min' => 12, 'stock_max' => 36, 'condition' => 'new'],
                ['name' => 'High-visibility Vest', 'unit' => 'pcs', 'price_min' => 14, 'price_max' => 28, 'stock_min' => 40, 'stock_max' => 120, 'condition' => 'new'],
                ['name' => 'Knee Pads Gel', 'unit' => 'pair', 'price_min' => 18, 'price_max' => 36, 'stock_min' => 20, 'stock_max' => 60, 'condition' => 'new'],
                ['name' => 'Welding Helmet', 'unit' => 'pcs', 'price_min' => 95, 'price_max' => 190, 'stock_min' => 8, 'stock_max' => 24, 'condition' => 'new'],
                ['name' => 'Safety Cones 18"', 'unit' => 'pcs', 'price_min' => 12, 'price_max' => 28, 'stock_min' => 20, 'stock_max' => 72, 'condition' => 'new'],
                ['name' => 'Barrier Tape Roll', 'unit' => 'roll', 'price_min' => 8, 'price_max' => 18, 'stock_min' => 24, 'stock_max' => 80, 'condition' => 'new'],
                ['name' => 'Work Gloves Cut-resistant', 'unit' => 'pair', 'price_min' => 18, 'price_max' => 42, 'stock_min' => 22, 'stock_max' => 66, 'condition' => 'new'],
                ['name' => 'Ear Plugs Foam', 'unit' => 'box', 'price_min' => 6, 'price_max' => 14, 'stock_min' => 30, 'stock_max' => 90, 'condition' => 'new'],
                ['name' => 'Safety Boot Covers', 'unit' => 'box', 'price_min' => 12, 'price_max' => 28, 'stock_min' => 24, 'stock_max' => 80, 'condition' => 'new'],
            ],
        ];

        foreach ($items as $categorySlug => $categoryItems) {
            if (! $categories->has($categorySlug)) {
                continue;
            }

            $category = $categories->get($categorySlug);
            $sequence = 1;

            foreach ($categoryItems as $row) {
                $skuPrefix = match ($categorySlug) {
                    'tools' => 'TLS',
                    'materials' => 'MAT',
                    'electrical' => 'ELE',
                    'plumbing' => 'PLM',
                    'safety-equipment' => 'SAF',
                    default => 'INV',
                };

                $itemCode = sprintf('%s-%03d', $skuPrefix, $sequence++);

                Inventory::updateOrCreate(
                    ['item_code' => $itemCode],
                    [
                        'item_code' => $itemCode,
                        'name' => $row['name'],
                        'description' => $row['description'] ?? $this->buildDescription($row['name'], $categorySlug),
                        'category_id' => $category->id,
                        'supplier_id' => $faker->randomElement($supplierIds) ?: null,
                        'quantity' => $faker->numberBetween($row['stock_min'], $row['stock_max']),
                        'unit' => $row['unit'],
                        'unit_cost' => $faker->randomFloat(2, $row['price_min'], $row['price_max']),
                        'status' => $faker->randomElement(['available', 'available', 'available', 'borrowed', 'maintenance']),
                        'condition' => $row['condition'] ?? $faker->randomElement(['new', 'good', 'fair']),
                        'location' => $faker->randomElement($locations),
                        'low_stock_threshold' => $faker->numberBetween(5, 15),
                        'date_added' => $faker->dateTimeBetween('-18 months', 'now')->format('Y-m-d'),
                        'is_active' => true,
                        'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function buildDescription(string $name, string $categorySlug): string
    {
        return match ($categorySlug) {
            'tools' => sprintf('%s manufactured for reliable on-site performance and heavy-duty use.', $name),
            'materials' => sprintf('%s supplied for construction and finishing applications.', $name),
            'electrical' => sprintf('%s designed for safe electrical installation and maintenance.', $name),
            'plumbing' => sprintf('%s engineered for plumbing repair and installation.', $name),
            'safety-equipment' => sprintf('%s built to meet safety and protective equipment standards.', $name),
            default => sprintf('%s for general inventory use.', $name),
        };
    }
}
