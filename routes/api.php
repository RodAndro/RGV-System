<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.rate:public', 'camel.json'])->group(function () {
    Route::get('/inventories', [InventoryController::class, 'index']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/books/{isbn}', [BookController::class, 'show']);
});
