<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshBestsellerStats extends Command
{
    protected $signature = 'books:refresh-bestseller-stats';
    protected $description = 'Refresh the bestseller materialized reporting table.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            DB::transaction(function () {
                DB::table('mv_bestseller_stats')->delete();
                DB::table('mv_bestseller_stats')->insertUsing(
                    ['category_id', 'book_count', 'total_stock', 'total_sales', 'avg_price', 'avg_rating', 'refreshed_at', 'created_at', 'updated_at'],
                    DB::table('books')
                        ->selectRaw('category_id, COUNT(*) book_count, SUM(stock) total_stock, SUM(sales_count) total_sales, AVG(price) avg_price, AVG(rating) avg_rating, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP')
                        ->where('is_active', true)
                        ->groupBy('category_id')
                );
            });

            $task->finish('success', 'Bestseller stats refreshed.');
            $this->info('Bestseller stats refreshed.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
