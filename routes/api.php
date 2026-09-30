<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Mobile\BorrowRequestController as MobileBorrowRequestController;
use App\Http\Controllers\Api\Mobile\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\Mobile\InventoryController as MobileInventoryController;
use App\Http\Controllers\Api\Mobile\ProfileController as MobileProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.rate:public', 'camel.json'])->group(function () {
    Route::get('/inventories', [InventoryController::class, 'index']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/books/{isbn}', [BookController::class, 'show']);
});

Route::prefix('v1/mobile')->group(function () {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/auth/mfa/verify', [MobileAuthController::class, 'verifyMfa'])->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'active.employee', 'throttle:120,1'])->group(function () {
        Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
        Route::get('/me', [MobileAuthController::class, 'me']);

        Route::get('/dashboard', [MobileDashboardController::class, 'index']);

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
