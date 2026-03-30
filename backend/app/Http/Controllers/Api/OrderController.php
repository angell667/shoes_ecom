<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\LowStockAlertNotification;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->load('items.product');

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        return DB::transaction(function () use ($request, $cartItems) {
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->effective_price * $item->quantity;
            });

            // Apply coupon if provided
            $coupon = null;
            $discount = 0;
            
            if ($request->coupon_code) {
                $coupon = Coupon::findValidCoupon($request->coupon_code);
                
                if (!$coupon) {
                    return response()->json(['error' => 'Invalid or expired coupon code'], 422);
                }
                
                if (!$coupon->meetsMinimumOrder($subtotal)) {
                    return response()->json([
                        'error' => 'Minimum order amount of $' . number_format($coupon->minimum_order, 2) . ' required for this coupon'
                    ], 422);
                }
                
                $discount = $coupon->calculateDiscount($subtotal);
            }

            $tax = $subtotal * 0.1; // 10% tax
            $shipping = $subtotal >= 100 ? 0 : 10; // Free shipping over $100
            $total = $subtotal + $tax + $shipping - $discount;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'pending',
                'notes' => $request->notes,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ]);

            // Mark coupon as used
            if ($coupon) {
                $coupon->markAsUsed();
            }

            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'price' => $cartItem->product->effective_price,
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                    'color' => $cartItem->color,
                ]);

                // Decrease stock using InventoryService
                $this->inventoryService->deductStock(
                    $cartItem->product,
                    $cartItem->quantity,
                    $request->user(),
                    'order_' . $order->id,
                    'Order #' . $order->order_number
                );
            }

            // Clear cart
            CartItem::where('user_id', $request->user()->id)->delete();

            // Send order confirmation notification
            $request->user()->notify(new OrderPlacedNotification($order));

            // Check for low stock and alert admin
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                if ($product->stock <= 5 && $product->stock > 0) {
                    // Get admin users and send low stock alert
                    $adminUsers = \App\Models\User::where('is_admin', true)->get();
                    foreach ($adminUsers as $admin) {
                        $admin->notify(new LowStockAlertNotification($product));
                    }
                }
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('items.product'),
            ], 201);
        });
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }
}
