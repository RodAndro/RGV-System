<?php

namespace Database\Seeders;

use App\Models\BorrowRequest;
use App\Models\BorrowItem;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class BorrowRequestSeeder extends Seeder
{
    public function run(): void
    {
        BorrowRequest::factory()
            ->count(20)
            ->create()
            ->each(function ($borrowRequest) {
                // Create 1-3 borrow items for each request
                $itemCount = rand(1, 3);
                for ($i = 0; $i < $itemCount; $i++) {
                    $inventory = Inventory::inRandomOrder()->first();
                    if ($inventory) {
                        BorrowItem::factory()->create([
                            'borrow_request_id' => $borrowRequest->id,
                            'inventory_id' => $inventory->id,
                        ]);
                    }
                }
            });
    }
}
