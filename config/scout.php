<?php

return [
    'driver' => env('SCOUT_DRIVER', 'database'),
    'queue' => env('SCOUT_QUEUE', true),
    'after_commit' => false,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('SCOUT_PREFIX', ''),
];
