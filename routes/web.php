<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemStatusController;
use App\Http\Controllers\SupermarketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('shopping-lists', ShoppingListController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('shopping-lists/{shopping_list}', [ShoppingListController::class, 'update'])
        ->name('shopping-lists.update');
    Route::post('shopping-lists/{shopping_list}/items', [ShoppingListController::class, 'addItems'])
        ->name('shopping-lists.items.store');

    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::put('products/{product}/sections', [ProductController::class, 'updateSections'])
        ->name('products.sections.update');

    Route::patch('shopping-lists/{shopping_list}/items/{shopping_list_item}/status', ShoppingListItemStatusController::class)
        ->name('shopping-lists.items.status');

    Route::get('supermarkets', [SupermarketController::class, 'index'])->name('supermarkets.index');
    Route::post('supermarkets', [SupermarketController::class, 'store'])->name('supermarkets.store');
    Route::put('supermarkets/{supermarket}', [SupermarketController::class, 'update'])->name('supermarkets.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', HomeController::class)->name('dashboard');
});
