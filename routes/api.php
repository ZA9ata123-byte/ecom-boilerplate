<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductMetafieldController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Auth\SimpleLoginController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', [HealthController::class, 'index'])->name('api.health');

// Auth بسيط بالإيميل/الباسورد للتست
Route::post('/auth/login', [SimpleLoginController::class, 'login'])->name('api.auth.login');
Route::post('/auth/logout', [SimpleLoginController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('api.auth.logout');

Route::get('/auth/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('api.auth.user');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::post('/products', [ProductController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.products.store');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');
Route::put('/products/{product}', [ProductController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.products.destroy');

// Product variants
Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])
    ->name('api.products.variants.index');
Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.products.variants.store');
Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.products.variants.update');
Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.products.variants.destroy');

// Product images
Route::get('/products/{product}/images', [ProductImageController::class, 'index'])
    ->name('api.products.images.index');
Route::post('/products/{product}/images', [ProductImageController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.products.images.store');
Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.products.images.destroy');

// Product metafields
Route::get('/products/{product}/metafields', [ProductMetafieldController::class, 'index'])
    ->name('api.products.metafields.index');
Route::post('/products/{product}/metafields', [ProductMetafieldController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.products.metafields.store');
Route::put('/products/{product}/metafields/{metafield}', [ProductMetafieldController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.products.metafields.update');
Route::delete('/products/{product}/metafields/{metafield}', [ProductMetafieldController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.products.metafields.destroy');

// Cart (يدعم المسجّل والضيف)
Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('api.cart.store');
Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('api.cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('api.cart.items.destroy');
