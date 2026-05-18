<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Console\Command;

class LoadTestBooks extends Command
{
    protected $signature = 'books:load-test {--users=50} {--requests=10}';
    protected $description = 'Simulate concurrent book catalog requests inside the application process.';

    public function handle(BookRepository $books): int
    {
        $users = (int) $this->option('users');
        $requests = (int) $this->option('requests');
        $isbn = Book::query()->value('isbn13');

        if (! $isbn) {
            $this->warn('No books found. Run MassBookSeeder first.');
            return self::FAILURE;
        }

        $durations = [];

        for ($u = 0; $u < $users; $u++) {
            for ($r = 0; $r < $requests; $r++) {
                $start = microtime(true);
                $r % 2 === 0 ? $books->catalog([], 25)->items() : $books->findByIsbn($isbn);
                $durations[] = round((microtime(true) - $start) * 1000, 2);
            }
        }

        $this->table(['Users', 'Requests', 'Avg ms', 'P95 ms', 'Max ms'], [[
            $users,
            count($durations),
            round(array_sum($durations) / count($durations), 2),
            $this->percentile($durations, 95),
            max($durations),
        ]]);

        return self::SUCCESS;
    }

    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = (int) ceil(($percentile / 100) * count($values)) - 1;

        return $values[max(0, $index)];
    }
}
