<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    /**
     * Get all coupons.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $coupons = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'coupons' => $coupons,
            'stats' => [
                'total' => Coupon::count(),
                'active' => Coupon::active()->count(),
                'expired' => Coupon::where('expires_at', '<', now())->count(),
                'total_usage' => Coupon::sum('used_count'),
            ],
        ]);
    }

    /**
     * Create a new coupon.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'minimum_order' => $request->minimum_order,
            'maximum_discount' => $request->maximum_discount,
            'usage_limit' => $request->usage_limit,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
        ]);

        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ], 201);
    }

    /**
     * Get a specific coupon.
     */
    public function show(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    /**
     * Update a coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0.01',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $coupon->update($request->all());

        return response()->json([
            'message' => 'Coupon updated successfully',
            'coupon' => $coupon->fresh(),
        ]);
    }

    /**
     * Delete a coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    /**
     * Toggle coupon active status.
     */
    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'message' => 'Coupon status updated',
            'coupon' => $coupon->fresh(),
        ]);
    }

    /**
     * Generate a random coupon code.
     */
    public function generateCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }
}