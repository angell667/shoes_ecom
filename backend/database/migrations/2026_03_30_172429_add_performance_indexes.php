<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('brand');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'stock']);
            $table->index('price');
            $table->index('created_at');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
            $table->index('order_number');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });

        // Cart items table indexes
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('product_id');
            $table->index(['user_id', 'product_id']);
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
