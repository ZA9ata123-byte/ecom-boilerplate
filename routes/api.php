<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
/*
|--------------------------------------------------------------------------
| API Routes
*/
// ========================================================================
// | Public Routes (Open to everyone)
// --- Authentication System ---
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
// --- Password Reset ---
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('api.forgot-password');
// --- Product Viewing ---
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');
// --- Cart for Guests and Users ---
Route::post('/cart/items', [CartController::class, 'store'])->name('api.cart.store');
// ✅ Health Check Endpoint (عام، خارج الميدلوير)
Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'service' => 'laravel',
        'time' => now()->toISOString(),
    ]);
})->name('api.ping');
// | Protected Routes (Require login/token)
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
    // --- Product Management (Admins Only) ---
    Route::middleware('is.admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });
});
Route::get('/health', fn() => response()->json(['ok'=>true,'time'=>now()->toIso8601String()]));
Route::get('/version', fn()=>response()->json(['php'=>PHP_VERSION,'laravel'=>app()->version()]));
Route::get('/health', fn()=>response()->json(['ok'=>true,'time'=>now()->toISOString()]));
Route::get('/health', HealthController::class);

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $user = $request->user();

    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
    }

    return response()->json([
        'message' => 'User logged out successfully!',
    ]);
})->name('api.logout');


Route::middleware('auth:sanctum')->post('/logout', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
    }

    return response()->json([
        'message' => 'User logged out successfully!',
    ]);
})->name('api.logout');


// ✅ راوت نظيف لارجاع اليوزر المصادق عليه عبر Sanctum
Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user('sanctum') ?? $request->user();
})->name('api.user');


// Product metafields (admin فقط عبر Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('products/{product}/metafields', [\App\Http\Controllers\Api\ProductMetafieldController::class, 'index'])
        ->name('api.products.metafields.index');

    Route::post('products/{product}/metafields', [\App\Http\Controllers\Api\ProductMetafieldController::class, 'store'])
        ->name('api.products.metafields.store');
});

// === Product metafields routes (admin via Sanctum) ===
Route::middleware('auth:sanctum')->group(function () {
    Route::get('products/{product}/metafields', [\App\Http\Controllers\Api\ProductMetafieldController::class, 'index'])
        ->name('api.products.metafields.index');

    Route::post('products/{product}/metafields', [\App\Http\Controllers\Api\ProductMetafieldController::class, 'store'])
        ->name('api.products.metafields.store');
});

// === Public SEO endpoint for a single product ===
Route::get('products/{product}/seo', function (\App\Models\Product $product) {
    return response()->json([
        'id'  => $product->id,
        'seo' => $product->getSeoMeta(),
    ]);
})->name('api.products.seo.show');


// === Product images (gallery) routes ===

// public: الزائر يقدر يشوف الجاليري ديال أي منتج
Route::get('products/{product}/images', [\App\Http\Controllers\Api\ProductImageController::class, 'index'])
    ->name('api.products.images.index');

// admin / authenticated: تعديل الجاليري
Route::middleware('auth:sanctum')->group(function () {
    Route::post('products/{product}/images', [\App\Http\Controllers\Api\ProductImageController::class, 'store'])
        ->name('api.products.images.store');

    Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Api\ProductImageController::class, 'destroy'])
        ->name('api.products.images.destroy');
});

// === Product variants routes ===

// public: الزائر يقدر يشوف المتغيّرات ديال المنتج
Route::get('products/{product}/variants', [\App\Http\Controllers\Api\ProductVariantController::class, 'index'])
    ->name('api.products.variants.index');

// admin / authenticated via Sanctum: إدارة المتغيّرات
Route::middleware('auth:sanctum')->group(function () {
    Route::post('products/{product}/variants', [\App\Http\Controllers\Api\ProductVariantController::class, 'store'])
        ->name('api.products.variants.store');

    Route::put('products/{product}/variants/{variant}', [\App\Http\Controllers\Api\ProductVariantController::class, 'update'])
        ->name('api.products.variants.update');

    Route::delete('products/{product}/variants/{variant}', [\App\Http\Controllers\Api\ProductVariantController::class, 'destroy'])
        ->name('api.products.variants.destroy');
});
