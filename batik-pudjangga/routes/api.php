<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Cart API
    Route::prefix('cart')->name('api.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cart}', [CartController::class, 'remove'])->name('remove');
        Route::get('/count', [CartController::class, 'count'])->name('count');
    });
    
    // Wishlist API
    Route::prefix('wishlist')->name('api.wishlist.')->group(function () {
        Route::post('/toggle/{product}', [CartController::class, 'toggleWishlist'])->name('toggle');
        Route::get('/check/{product}', [CartController::class, 'checkWishlist'])->name('check');
    });
});

// Public API
Route::prefix('shipping')->name('api.shipping.')->group(function () {
    Route::get('/provinces', [ShippingController::class, 'provinces'])->name('provinces');
    Route::get('/cost', [ShippingController::class, 'getCost'])->name('cost');
});

Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/search', [ProductController::class, 'search'])->name('search');
    Route::get('/{product}/variants', [ProductController::class, 'variants'])->name('variants');
});