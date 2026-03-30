<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
| All routes require admin authentication
*/

Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Protected admin routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Auth
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/user', [AdminAuthController::class, 'user']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'stats']);

        // Products
        Route::apiResource('products', AdminProductController::class);
        Route::patch('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured']);
        Route::patch('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive']);

        // Categories
        Route::apiResource('categories', AdminCategoryController::class);

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::patch('/orders/{order}/payment', [AdminOrderController::class, 'updatePaymentStatus']);

        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    });
});
