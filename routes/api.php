<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShoppingListController;
use App\Http\Controllers\Api\SupermarketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);

    // Supermarkets
    Route::get('/supermarkets', [SupermarketController::class, 'index']);

    // Shopping Lists
    Route::get('/shopping-lists', [ShoppingListController::class, 'index']);
    Route::post('/shopping-lists', [ShoppingListController::class, 'store']);
    Route::get('/shopping-lists/{shopping_list}', [ShoppingListController::class, 'show']);
    Route::match(['put', 'patch'], '/shopping-lists/{shopping_list}', [ShoppingListController::class, 'update']);

    // Toggle Item Status
    Route::patch('/shopping-lists/{shopping_list}/items/{item}/status', [ShoppingListController::class, 'toggleItem']);
});
