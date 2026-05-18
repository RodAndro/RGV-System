<?php

namespace App\Console\Commands;

use App\Jobs\WarmTopBooksCache;
use Illuminate\Console\Command;

class WarmBooksCacheCommand extends Command
{
    protected $signature = 'books:warm-cache {--sync}';
    protected $description = 'Warm top-books cache per category.';

    public function handle(): int
    {
        $this->option('sync') ? WarmTopBooksCache::dispatchSync() : WarmTopBooksCache::dispatch();
        $this->info('Book cache warmup dispatched.');

        return self::SUCCESS;
    }
}
