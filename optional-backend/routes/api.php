<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPhotoController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC ROUTES (No login required)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// 2. PRIVATE ROUTES (Requires Sanctum Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Auth & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('admin')->group(function () {
        // Protected Product Management (Store, Update, Delete)
        Route::post('/products', [ProductController::class, 'store']);

        // Accept PUT, PATCH, and POST for updates to avoid any method mismatch errors
        Route::match(['put', 'patch', 'post'], '/products/{product}', [ProductController::class, 'update']);

        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Protected Category Management (Store, Update, Delete)
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // Cart Management
        Route::apiResource('carts', CartController::class);

        // Other Protected Resources
        Route::apiResource('product-photos', ProductPhotoController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('payments', PaymentController::class);
        Route::apiResource('reviews', ReviewController::class);

        // Nested Resource for Order Products
        Route::apiResource('orders.products', OrderProductController::class);
    });
});
