<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\InventoryCategory;
use App\Repositories\BookRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BenchmarkBookQueries extends Command
{
    protected $signature = 'books:benchmark {--iterations=5} {--json}';
    protected $description = 'Measure book query performance against target response times.';

    public function handle(BookRepository $books): int
    {
        $iterations = (int) $this->option('iterations');
        $isbn = Book::query()->value('isbn13');
        $categoryId = InventoryCategory::query()->value('id');

        if (! $isbn || ! $categoryId) {
            $this->warn('No books/categories found. Run MassBookSeeder first.');
            return self::FAILURE;
        }

        $results = [
            'isbn_lookup' => $this->time(fn () => $books->findByIsbn($isbn), $iterations),
            'catalog' => $this->time(fn () => $books->catalog([], 50)->items(), $iterations),
            'category_filter' => $this->time(fn () => $books->catalog(['category_id' => $categoryId], 50)->items(), $iterations),
            'full_text_search' => $this->time(fn () => $books->search('Performance', 25)->items(), $iterations),
            'cached_top_books' => $this->time(fn () => $books->topByCategory($categoryId, 20), $iterations),
        ];

        $payload = [
            'database' => config('database.default'),
            'cache' => config('cache.default'),
            'book_count' => Book::count(),
            'iterations' => $iterations,
            'results_ms' => $results,
            'targets_ms' => config('books.performance_targets'),
            'generated_at' => now()->toDateTimeString(),
        ];

        Storage::disk('local')->put('benchmarks/books-' . now()->format('Ymd-His') . '.json', json_encode($payload, JSON_PRETTY_PRINT));

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        } else {
            $this->table(['Query', 'Avg ms', 'Min ms', 'Max ms'], collect($results)->map(fn ($row, $name) => [$name, $row['avg'], $row['min'], $row['max']])->all());
        }

        return self::SUCCESS;
    }

    private function time(callable $callback, int $iterations): array
    {
        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $callback();
            $times[] = round((microtime(true) - $start) * 1000, 2);
        }

        return [
            'avg' => round(array_sum($times) / count($times), 2),
            'min' => min($times),
            'max' => max($times),
        ];
    }
}
