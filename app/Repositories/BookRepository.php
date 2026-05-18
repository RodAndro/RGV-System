<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\QueryPerformanceLog;
use App\Services\CacheService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class BookRepository
{
    public function __construct(private readonly CacheService $cache)
    {
    }

    public function findByIsbn(string $isbn): ?Book
    {
        return $this->measure('isbn_lookup', function () use ($isbn) {
            return $this->cache->rememberBookQuery(
                'book:isbn:' . $isbn,
                fn () => Book::query()
                    ->select(['id', 'isbn', 'isbn13', 'title', 'category_id', 'author', 'price', 'stock', 'is_active'])
                    ->with('category:id,name,slug')
                    ->where(fn ($query) => $query->where('isbn13', $isbn)->orWhere('isbn', $isbn))
                    ->first(),
                900
            );
        });
    }

    public function catalog(array $filters = [], int $perPage = 50): CursorPaginator
    {
        $query = Book::query()
            ->select(['id', 'isbn13', 'title', 'category_id', 'author', 'format', 'price', 'stock', 'rating', 'is_active', 'created_at'])
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->when($filters['category_id'] ?? null, fn ($query, $value) => $query->where('category_id', $value))
            ->when($filters['min_price'] ?? null, fn ($query, $value) => $query->where('price', '>=', $value))
            ->when($filters['max_price'] ?? null, fn ($query, $value) => $query->where('price', '<=', $value))
            ->when($filters['format'] ?? null, fn ($query, $value) => $query->where('format', $value))
            ->orderBy('id');

        return $this->measure('catalog_cursor', fn () => $query->cursorPaginate($perPage));
    }

    public function search(string $term, int $perPage = 25): CursorPaginator
    {
        $query = Book::query()
            ->select(['id', 'isbn13', 'title', 'category_id', 'author', 'price', 'stock', 'rating'])
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                if (DB::getDriverName() === 'mysql') {
                    $query->whereRaw('MATCH(title, author, description) AGAINST (? IN BOOLEAN MODE)', [$term . '*']);
                } else {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('author', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }
            })
            ->orderByDesc('sales_count')
            ->orderBy('id');

        return $this->measure('full_text_search', fn () => $query->cursorPaginate($perPage));
    }

    public function topByCategory(int $categoryId, int $limit = 20)
    {
        return $this->cache->rememberCategoryBooks(
            $categoryId,
            "books:category:{$categoryId}:top:{$limit}",
            fn () => Book::query()
                ->select(['id', 'isbn13', 'title', 'category_id', 'author', 'price', 'sales_count', 'rating'])
                ->where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->orderByDesc('rating')
                ->limit($limit)
                ->get(),
            1800
        );
    }

    private function measure(string $name, callable $callback): mixed
    {
        $start = microtime(true);
        $result = $callback();
        $duration = (int) round((microtime(true) - $start) * 1000);

        QueryPerformanceLog::create([
            'name' => $name,
            'duration_ms' => $duration,
            'rows_returned' => method_exists($result, 'count') ? $result->count() : ($result ? 1 : 0),
            'cache_hit' => false,
            'context' => ['target_ms' => config("books.performance_targets.{$name}")],
        ]);

        return $result;
    }
}
