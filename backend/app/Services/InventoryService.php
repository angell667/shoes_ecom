<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct stock from a product.
     */
    public function deductStock(Product $product, int $quantity, ?User $user = null, ?string $reference = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($product, $quantity, $user, $reference, $notes) {
            $stockBefore = $product->stock;
            
            if ($stockBefore < $quantity) {
                Log::warning('Insufficient stock for deduction', [
                    'product_id' => $product->id,
                    'requested' => $quantity,
                    'available' => $stockBefore,
                ]);
                return false;
            }
            
            $product->decrement('stock', $quantity);
            $stockAfter = $stockBefore - $quantity;
            
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'quantity' => -$quantity,
                'type' => 'sale',
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $reference,
                'notes' => $notes,
            ]);
            
            // Check if low stock alert needed
            $this->checkLowStock($product);
            
            return true;
        });
    }

    /**
     * Add stock to a product (restock).
     */
    public function addStock(Product $product, int $quantity, ?User $user = null, ?string $reference = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($product, $quantity, $user, $reference, $notes) {
            $stockBefore = $product->stock;
            
            $product->increment('stock', $quantity);
            $stockAfter = $stockBefore + $quantity;
            
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'quantity' => $quantity,
                'type' => 'restock',
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $reference,
                'notes' => $notes,
            ]);
            
            return true;
        });
    }

    /**
     * Adjust stock (for corrections).
     */
    public function adjustStock(Product $product, int $newStock, ?User $user = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($product, $newStock, $user, $notes) {
            $stockBefore = $product->stock;
            $quantity = $newStock - $stockBefore;
            
            $product->update(['stock' => $newStock]);
            
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'quantity' => $quantity,
                'type' => 'adjustment',
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => $notes,
            ]);
            
            return true;
        });
    }

    /**
     * Handle return (add stock back).
     */
    public function processReturn(Product $product, int $quantity, ?User $user = null, ?string $reference = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($product, $quantity, $user, $reference, $notes) {
            $stockBefore = $product->stock;
            
            $product->increment('stock', $quantity);
            $stockAfter = $stockBefore + $quantity;
            
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'quantity' => $quantity,
                'type' => 'return',
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $reference,
                'notes' => $notes,
            ]);
            
            return true;
        });
    }

    /**
     * Get inventory history for a product.
     */
    public function getProductHistory(Product $product, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryLog::where('product_id', $product->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('is_active', true)
            ->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('is_active', true)
            ->where('stock', '=', 0)
            ->get();
    }

    /**
     * Check and send low stock alert if needed.
     */
    protected function checkLowStock(Product $product, int $threshold = 5): void
    {
        if ($product->stock <= $threshold && $product->stock > 0) {
            Log::warning('Low stock alert', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->stock,
            ]);
            
            // Could trigger notification here
            // event(new LowStockAlert($product));
        }
    }

    /**
     * Get inventory statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_products' => Product::where('is_active', true)->count(),
            'total_stock_value' => Product::where('is_active', true)
                ->sum(DB::raw('stock * price')),
            'low_stock_count' => $this->getLowStockProducts()->count(),
            'out_of_stock_count' => $this->getOutOfStockProducts()->count(),
            'recent_activity' => InventoryLog::with('product:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }
}