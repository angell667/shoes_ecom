<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

// Products - with rate limiting for search
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/brands', [ProductController::class, 'brands']);
Route::middleware('throttle:120,1')->get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/navbar', [CategoryController::class, 'navbar']);
Route::get('/categories/{slug}', [CategoryController::class, 'showBySlug']);

// Reviews (public - viewing only)
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('/reviews/{review}/helpful', [ReviewController::class, 'markHelpful']);

// Auth (public) - with rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Google OAuth - with session middleware for Socialite
Route::middleware('web')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| Protected API Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth (protected)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    // Payments - Stripe
    Route::post('/payments/create-intent', [PaymentController::class, 'createIntent']);
    Route::get('/payments/{order}', [PaymentController::class, 'show']);
    Route::post('/orders/{order}/refund', [PaymentController::class, 'refund']);
    
    // Payments - PayPal
    Route::post('/payments/paypal/create-order', [PaymentController::class, 'createPayPalOrder']);
    Route::post('/payments/paypal/capture', [PaymentController::class, 'capturePayPal']);
    Route::get('/payments/paypal/config', [PaymentController::class, 'paypalConfig']);
    Route::post('/payments/paypal/cancel/{order}', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');
    
    // Reviews (protected - creating, updating, deleting)
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
    Route::put('/products/{product}/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/products/{product}/reviews/{review}', [ReviewController::class, 'destroy']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/add/{product}', [WishlistController::class, 'add']);
    Route::delete('/wishlist/remove/{product}', [WishlistController::class, 'remove']);
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/check/{product}', [WishlistController::class, 'check']);
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear']);
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart']);
    
    // Coupons (apply only needs auth)
    Route::post('/coupons/apply', [CouponController::class, 'apply']);
});

// Public coupons (for display and validation)
Route::get('/coupons', [CouponController::class, 'index']);
Route::post('/coupons/validate', [CouponController::class, 'validate']);

// Public payment methods (for checkout)
Route::get('/payment-methods', [\App\Http\Controllers\Api\Admin\PaymentSettingsController::class, 'methods']);

// Stripe Webhook (outside auth middleware)
Route::post('/webhooks/stripe', [PaymentController::class, 'webhook']);

// Admin routes
require __DIR__.'/admin.php';
