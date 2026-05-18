<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function rememberBookQuery(string $key, callable $callback, int $ttl = 300): mixed
    {
        return $this->tags(['books'])->remember($key, $ttl, $callback);
    }

    public function rememberCategoryBooks(int $categoryId, string $key, callable $callback, int $ttl = 600): mixed
    {
        return $this->tags(['books', "category:{$categoryId}"])->remember($key, $ttl, $callback);
    }

    public function flushBooks(?int $categoryId = null): void
    {
        $this->tags($categoryId ? ['books', "category:{$categoryId}"] : ['books'])->flush();
    }

    private function tags(array $tags)
    {
        try {
            return Cache::tags($tags);
        } catch (\BadMethodCallException) {
            return Cache::store();
        }
    }
}
