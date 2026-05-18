<?php

return [
    'performance_targets' => [
        'isbn_lookup' => env('BOOK_TARGET_ISBN_MS', 50),
        'catalog_cursor' => env('BOOK_TARGET_CATALOG_MS', 100),
        'category_filter' => env('BOOK_TARGET_CATEGORY_MS', 150),
        'full_text_search' => env('BOOK_TARGET_SEARCH_MS', 300),
        'cached_query' => env('BOOK_TARGET_CACHE_MS', 10),
    ],

    'sharding' => [
        'enabled' => env('BOOK_SHARDING_ENABLED', false),
        'strategy' => env('BOOK_SHARDING_STRATEGY', 'id_range'),
    ],
];
