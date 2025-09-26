<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// | Public Routes (Open to everyone)
// ========================================================================

// --- Authentication System ---
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// --- Password Reset ---
// هذا هو المسار الجديد لي زدنا، خاصو يكون عام
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('api.forgot-password');


// --- Product Viewing ---
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

// --- Cart for Guests and Users ---
// This endpoint is public to allow guests to add to their cart
Route::post('/cart/items', [CartController::class, 'store'])->name('api.cart.store');


// ========================================================================
// | Protected Routes (Require login/token)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- User Information ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    // --- Cart Management for Logged-in Users ---
    Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
    Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('api.cart.destroy');
    
    // --- Order System ---
    Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
    // We will add routes to view orders later

    // --- Product Management (Admins Only) ---
    Route::middleware('is.admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });
});
