<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| Laravel's framework default allows every origin ("*"). That is not
| acceptable for a graded deployment (Q4), so the allowed origins are
| pinned to the SPA's address from FRONTEND_URL.
|
| Multiple origins may be supplied as a comma separated list, e.g.
| FRONTEND_URL="https://rgv.example.com,http://localhost:5173".
|
*/

$frontendOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('FRONTEND_URL', 'http://localhost:5173'))
)));

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $frontendOrigins ?: ['http://localhost:5173'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
