<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Validate and apply coupon code.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::findValidCoupon($request->code);

        if (!$coupon) {
            return response()->json(['error' => 'Invalid or expired coupon code'], 404);
        }

        if (!$coupon->meetsMinimumOrder($request->subtotal)) {
            return response()->json([
                'error' => 'Minimum order amount of $' . number_format($coupon->minimum_order, 2) . ' required'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon applied successfully',
        ]);
    }

    /**
     * Validate coupon without applying (for cart preview).
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::findValidCoupon($request->code);

        if (!$coupon) {
            return response()->json(['valid' => false, 'error' => 'Invalid or expired coupon code']);
        }

        if (!$coupon->meetsMinimumOrder($request->subtotal)) {
            return response()->json([
                'valid' => false,
                'error' => 'Minimum order amount of $' . number_format($coupon->minimum_order, 2) . ' required'
            ]);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
        ]);
    }

    /**
     * Get all active coupons (for display purposes).
     */
    public function index()
    {
        $coupons = Coupon::active()
            ->whereNull('usage_limit')
            ->orWhereColumn('used_count', '<', 'usage_limit')
            ->get()
            ->map(function ($coupon) {
                return [
                    'code' => $coupon->code,
                    'description' => $coupon->type === 'percentage' 
                        ? $coupon->value . '% off' 
                        : '$' . number_format($coupon->value, 2) . ' off',
                    'minimum_order' => $coupon->minimum_order,
                    'expires_at' => $coupon->expires_at,
                ];
            });

        return response()->json($coupons);
    }
}