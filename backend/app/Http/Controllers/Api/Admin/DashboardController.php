<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // Basic counts
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalUsers = User::where('is_admin', false)->count();
        $totalReviews = Review::count();
        $totalCoupons = Coupon::count();
        $totalWishlistItems = Wishlist::count();
        
        // Order stats
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $completedOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        // Revenue stats
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $monthlyRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('total');
        $averageOrderValue = Order::where('status', '!=', 'cancelled')->avg('total') ?? 0;
        
        // Payment stats
        $totalPayments = Payment::count();
        $completedPayments = Payment::where('status', 'completed')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $failedPayments = Payment::where('status', 'failed')->count();
        $refundedPayments = Payment::where('status', 'refunded')->orWhere('status', 'partially_refunded')->count();
        
        // Review stats
        $pendingReviews = Review::where('is_approved', false)->count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $averageRating = Review::where('is_approved', true)->avg('rating') ?? 0;
        
        // Coupon stats
        $activeCoupons = Coupon::where('is_active', true)->count();
        $usedCoupons = Coupon::where('used_count', '>', 0)->count();
        $totalCouponUsage = Coupon::sum('used_count');
        
        // Inventory stats
        $lowStockCount = Product::where('stock', '<=', 10)->where('stock', '>', 0)->where('is_active', true)->count();
        $outOfStockCount = Product::where('stock', 0)->where('is_active', true)->count();
        $totalStockValue = Product::where('is_active', true)->sum(DB::raw('stock * price'));
        
        // Recent data
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $recentProducts = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();
        
        $outOfStockProducts = Product::where('stock', 0)
            ->where('is_active', true)
            ->limit(10)
            ->get();
        
        // Pending reviews (unapproved)
        $pendingReviewList = Review::where('is_approved', false)
            ->with(['user:id,name', 'product:id,name,slug,image'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Recent users
        $recentUsers = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Recent payments
        $recentPayments = Payment::with(['order:id,order_number', 'order.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Recent inventory logs
        $recentInventoryLogs = InventoryLog::with(['product:id,name', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Recent coupons
        $recentCoupons = Coupon::orderBy('created_at', 'desc')->limit(10)->get();
        
        // Order status distribution
        $orderStatusDistribution = [
            'pending' => $pendingOrders,
            'processing' => $processingOrders,
            'shipped' => $shippedOrders,
            'delivered' => $completedOrders,
            'cancelled' => $cancelledOrders,
        ];
        
        // Top selling products (by quantity)
        $topSellingProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->with('product:id,name,slug,image,price')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Revenue by month (last 6 months)
        $monthlyRevenueData = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('strftime("%Y", created_at) as year'),
                DB::raw('strftime("%m", created_at) as month'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'stats' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_orders' => $totalOrders,
                'total_users' => $totalUsers,
                'total_reviews' => $totalReviews,
                'total_coupons' => $totalCoupons,
                'total_wishlist_items' => $totalWishlistItems,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'shipped_orders' => $shippedOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'average_order_value' => round($averageOrderValue, 2),
                'order_status_distribution' => $orderStatusDistribution,
                // Payment stats
                'total_payments' => $totalPayments,
                'completed_payments' => $completedPayments,
                'pending_payments' => $pendingPayments,
                'failed_payments' => $failedPayments,
                'refunded_payments' => $refundedPayments,
                // Review stats
                'pending_reviews' => $pendingReviews,
                'approved_reviews' => $approvedReviews,
                'average_rating' => round($averageRating, 2),
                // Coupon stats
                'active_coupons' => $activeCoupons,
                'used_coupons' => $usedCoupons,
                'total_coupon_usage' => $totalCouponUsage,
                // Inventory stats
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_stock_value' => round($totalStockValue, 2),
            ],
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'pending_reviews' => $pendingReviewList,
            'recent_users' => $recentUsers,
            'recent_payments' => $recentPayments,
            'recent_inventory_logs' => $recentInventoryLogs,
            'recent_coupons' => $recentCoupons,
            'top_selling_products' => $topSellingProducts,
            'monthly_revenue_data' => $monthlyRevenueData,
        ]);
    }
}
