<?php

namespace App\Jobs;

use App\Models\InventoryCategory;
use App\Repositories\BookRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class WarmTopBooksCache implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(BookRepository $books): void
    {
        InventoryCategory::query()
            ->select(['id'])
            ->where('is_active', true)
            ->chunkById(100, function ($categories) use ($books) {
                foreach ($categories as $category) {
                    $books->topByCategory($category->id, 20);
                }
            });
    }
}
