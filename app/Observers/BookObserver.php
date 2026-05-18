<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\SearchIndexingQueue;
use App\Services\CacheService;

class BookObserver
{
    public function saved(Book $book): void
    {
        app(CacheService::class)->flushBooks($book->category_id);
        SearchIndexingQueue::updateOrCreate(
            ['book_id' => $book->id],
            ['status' => 'pending', 'failure_message' => null]
        );
    }

    public function deleted(Book $book): void
    {
        app(CacheService::class)->flushBooks($book->category_id);
    }
}
