<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class AdminInventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get all inventory logs with pagination and filtering.
     */
    public function index(Request $request)
    {
        $query = InventoryLog::with(['product:id,name,slug,image,stock', 'user:id,name']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    /**
     * Get low stock products.
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->get('threshold', 10);

        $products = Product::where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->with('category:id,name')
            ->orderBy('stock', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    /**
     * Get out of stock products.
     */
    public function outOfStock(Request $request)
    {
        $products = Product::where('stock', 0)
            ->where('is_active', true)
            ->with('category:id,name')
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    /**
     * Get inventory stats.
     */
    public function stats()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('stock', '<=', 10)->where('stock', '>', 0)->where('is_active', true)->count();
        $outOfStockCount = Product::where('stock', 0)->where('is_active', true)->count();
        $totalStockValue = Product::where('is_active', true)->sum('stock * price');
        $totalItemsInStock = Product::where('is_active', true)->sum('stock');

        return response()->json([
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_stock_value' => round($totalStockValue, 2),
            'total_items_in_stock' => $totalItemsInStock,
        ]);
    }

    /**
     * Get inventory logs for a specific product.
     */
    public function productLogs(Product $product, Request $request)
    {
        $logs = $product->inventoryLogs()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'product' => $product->only(['id', 'name', 'slug', 'stock', 'price']),
            'logs' => $logs,
        ]);
    }

    /**
     * Manually adjust inventory for a product.
     */
    public function adjustInventory(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($request->product_id);
        $oldStock = $product->stock;

        if ($this->inventoryService->adjustStock($product, $request->new_stock, $request->user(), $request->notes)) {
            $product->refresh();
            
            return response()->json([
                'message' => 'Inventory adjusted successfully',
                'product' => $product->only(['id', 'name', 'stock']),
                'old_stock' => $oldStock,
                'new_stock' => $product->stock,
            ]);
        }

        return response()->json([
            'message' => 'Failed to adjust inventory',
        ], 500);
    }
}