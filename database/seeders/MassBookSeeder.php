<?php

namespace Database\Seeders;

use Database\Factories\BookFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassBookSeeder extends Seeder
{
    public function run(): void
    {
        $target = (int) env('MASS_BOOK_SEED_COUNT', 1000000);
        $chunkSize = (int) env('MASS_BOOK_SEED_CHUNK', 5000);
        $categoryIds = BookFactory::categoryIds();
        $formats = ['paperback', 'hardcover', 'ebook', 'audiobook'];
        $now = now();
        $inserted = 0;
        $startedAt = microtime(true);

        DB::disableQueryLog();

        while ($inserted < $target) {
            $rows = [];
            $limit = min($chunkSize, $target - $inserted);

            for ($i = 0; $i < $limit; $i++) {
                $sequence = $inserted + $i + 1;
                $format = $formats[$sequence % count($formats)];
                $isbn13 = BookFactory::isbn13($sequence);
                $title = 'Performance Book ' . str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);

                $rows[] = [
                    'isbn' => substr($isbn13, 0, 3) . '-' . substr($isbn13, 3, 1) . '-' . substr($isbn13, 4, 3) . '-' . substr($isbn13, 7, 5) . '-' . substr($isbn13, 12, 1),
                    'isbn13' => $isbn13,
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'category_id' => $categoryIds[$sequence % count($categoryIds)],
                    'author' => 'Author ' . ($sequence % 10000),
                    'publisher' => 'Publisher ' . ($sequence % 500),
                    'format' => $format,
                    'price' => BookFactory::priceForFormat($format),
                    'stock' => $sequence % 750,
                    'sales_count' => ($sequence * 13) % 250000,
                    'rating' => round(2.5 + (($sequence % 250) / 100), 2),
                    'description' => 'Synthetic benchmark record for catalog search, category filtering, ISBN lookup, exports, and cache warmup.',
                    'is_active' => $sequence % 20 !== 0,
                    'published_at' => $now->copy()->subDays($sequence % 7300)->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('books')->insert($rows);
            $inserted += $limit;
            $this->command?->info("Seeded {$inserted}/{$target} books. Memory: " . round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB');
            unset($rows);
        }

        $seconds = round(microtime(true) - $startedAt, 2);
        $this->command?->info("Seeded {$inserted} books in {$seconds}s.");
    }
}
