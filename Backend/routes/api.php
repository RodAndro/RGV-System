<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Mobile\BorrowRequestController as MobileBorrowRequestController;
use App\Http\Controllers\Api\Mobile\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\Mobile\InventoryController as MobileInventoryController;
use App\Http\Controllers\Api\Mobile\ProfileController as MobileProfileController;
use App\Http\Controllers\Api\V1\AiInsightController as V1AiInsightController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\BookingController as V1BookingController;
use App\Http\Controllers\Api\V1\BorrowRequestController as V1BorrowRequestController;
use App\Http\Controllers\Api\V1\InventoryCategoryController as V1InventoryCategoryController;
use App\Http\Controllers\Api\V1\InventoryController as V1InventoryController;
use App\Support\HealthProbe;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (HealthProbe $probe) {
    $database = $probe->databaseStatus();
    $status = $database === 'ok' ? 200 : 503;

    if ($status === 503) {
        Log::warning('Health check database connectivity failed.');
    }

    $commit = env('APP_COMMIT') ?: (getenv('GITHUB_SHA') ?: null);

    return response()->json([
        'status' => $status === 200 ? 'ok' : 'degraded',
        'version' => config('app.version', '1.0.0'),
        'commit' => $commit,
        'database' => $database,
        'checked_at' => now()->toIso8601String(),
    ], $status);
});

Route::get('/openapi.yaml', function () {
    $path = base_path('../docs/api/openapi.yaml');

    if (! File::exists($path)) {
        return response()->json([
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'OpenAPI specification is not available.',
                'details' => [],
            ],
        ], 404);
    }

    return response(File::get($path), 200, [
        'Content-Type' => 'application/yaml; charset=utf-8',
        'Cache-Control' => 'public, max-age=300',
    ]);
})->name('api.openapi');

/*
|--------------------------------------------------------------------------
| API documentation (Swagger UI)
|--------------------------------------------------------------------------
| Serves an interactive Swagger UI against /api/openapi.yaml so reviewers
| can exercise endpoints from the browser (B7). Falls back to the raw
| specification when the YAML file is missing, so this route can never 500.
*/
Route::get('/docs', function () {
    $spec = base_path('../docs/api/openapi.yaml');

    if (! File::exists($spec)) {
        return response('OpenAPI specification not found at docs/api/openapi.yaml', 404, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RGV System API Reference</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui.css" />
  <style>
    body { margin: 0; font-family: system-ui, sans-serif; }
    .topbar { display: none; }
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui-bundle.js" crossorigin></script>
  <script>
    window.addEventListener('load', function () {
      window.ui = SwaggerUIBundle({
        url: '/api/openapi.yaml',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis],
        layout: 'BaseLayout'
      });
    });
  </script>
</body>
</html>
HTML;

    return response($html, 200, [
        'Content-Type' => 'text/html; charset=utf-8',
        'Cache-Control' => 'public, max-age=300',
    ]);
})->name('api.docs');

Route::middleware(['api.rate:public', 'camel.json'])->group(function () {
    Route::get('/inventories', [InventoryController::class, 'index']);
});

Route::prefix('v1/mobile')->group(function () {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/auth/mfa/verify', [MobileAuthController::class, 'verifyMfa'])->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'active.employee', 'token.ability:employee', 'throttle:120,1'])->group(function () {
        Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
        Route::get('/me', [MobileAuthController::class, 'me']);

        Route::get('/dashboard', [MobileDashboardController::class, 'index']);
        Route::post('/dashboard/recent-requests/{requestId}/dismiss', [MobileDashboardController::class, 'dismissRecentRequest'])
            ->whereNumber('requestId');

        Route::get('/inventory/lookup', [MobileInventoryController::class, 'lookup']);
        Route::get('/inventory/search', [MobileInventoryController::class, 'search']);

        Route::get('/borrow-requests', [MobileBorrowRequestController::class, 'index']);
        Route::post('/borrow-requests', [MobileBorrowRequestController::class, 'store']);
        Route::get('/borrow-requests/returnable', [MobileBorrowRequestController::class, 'returnableItems']);
        Route::get('/borrow-requests/{borrowRequest}', [MobileBorrowRequestController::class, 'show']);
        Route::post('/borrow-requests/{borrowRequest}/cancel', [MobileBorrowRequestController::class, 'cancel']);
        Route::post('/borrow-requests/{borrowRequest}/return', [MobileBorrowRequestController::class, 'return']);

        Route::patch('/profile', [MobileProfileController::class, 'update']);
        Route::put('/password', [MobileProfileController::class, 'changePassword']);
    });
});

/*
|--------------------------------------------------------------------------
| Versioned REST API (/api/v1)
|--------------------------------------------------------------------------
| Plural, lower-case, hyphenated resources with standard HTTP verbs and
| status codes (B1, B2). Guarded by Sanctum bearer tokens plus a
| token-safe role middleware (B4). All list endpoints share the
| page/limit/sort/q + meta contract (B6).
*/
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [V1AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::get('/auth/me', [V1AuthController::class, 'me']);
        Route::post('/auth/logout', [V1AuthController::class, 'logout']);

        // Any authenticated role may read.
        Route::middleware('api.role:admin,employee')->group(function () {
            Route::get('/inventories', [V1InventoryController::class, 'index'])->name('api.v1.inventories.index');
            Route::get('/inventories/{inventory}', [V1InventoryController::class, 'show'])->name('api.v1.inventories.show');
            Route::get('/inventory-categories', [V1InventoryCategoryController::class, 'index'])->name('api.v1.inventory-categories.index');
            Route::get('/inventory-categories/{inventoryCategory}', [V1InventoryCategoryController::class, 'show'])->name('api.v1.inventory-categories.show');

            Route::get('/borrow-requests', [V1BorrowRequestController::class, 'index'])->name('api.v1.borrow-requests.index');
            Route::post('/borrow-requests', [V1BorrowRequestController::class, 'store'])->name('api.v1.borrow-requests.store');
            Route::get('/borrow-requests/{borrowRequest}', [V1BorrowRequestController::class, 'show'])->name('api.v1.borrow-requests.show');
        });

        // Admin-only writes and reporting reads.
        Route::middleware('api.role:admin')->group(function () {
            Route::post('/inventories', [V1InventoryController::class, 'store'])->name('api.v1.inventories.store');
            Route::match(['put', 'patch'], '/inventories/{inventory}', [V1InventoryController::class, 'update'])->name('api.v1.inventories.update');
            Route::delete('/inventories/{inventory}', [V1InventoryController::class, 'destroy'])->name('api.v1.inventories.destroy');

            Route::match(['put', 'patch'], '/borrow-requests/{borrowRequest}', [V1BorrowRequestController::class, 'update'])->name('api.v1.borrow-requests.update');
            Route::delete('/borrow-requests/{borrowRequest}', [V1BorrowRequestController::class, 'destroy'])->name('api.v1.borrow-requests.destroy');

            Route::get('/bookings', [V1BookingController::class, 'index'])->name('api.v1.bookings.index');
            Route::get('/bookings/{booking}', [V1BookingController::class, 'show'])->name('api.v1.bookings.show');

            // AI integration (T1-T6). Reachable only through the API, so the
            // client never holds model files or service keys (T2).
            Route::get('/ai/insights', [V1AiInsightController::class, 'insights'])->name('api.v1.ai.insights');
            Route::get('/ai/forecast', [V1AiInsightController::class, 'forecast'])->name('api.v1.ai.forecast');
            Route::get('/ai/inventory-recommendations', [V1AiInsightController::class, 'inventoryRecommendations'])
                ->name('api.v1.ai.inventory-recommendations');
        });
    });
});
