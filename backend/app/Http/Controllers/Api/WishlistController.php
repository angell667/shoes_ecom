<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist.
     */
    public function index(Request $request)
    {
        $wishlistItems = Wishlist::where('user_id', $request->user()->id)
            ->with('product:id,name,slug,image,price,sale_price,stock,is_active')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'items' => $wishlistItems,
            'count' => $wishlistItems->count(),
        ]);
    }

    /**
     * Add product to wishlist.
     */
    public function add(Request $request, Product $product)
    {
        // Check if product exists and is active
        if (!$product->is_active) {
            return response()->json(['error' => 'Product not available'], 404);
        }

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Product already in wishlist'], 422);
        }

        Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Product added to wishlist',
            'count' => Wishlist::where('user_id', $request->user()->id)->count(),
        ], 201);
    }

    /**
     * Remove product from wishlist.
     */
    public function remove(Request $request, Product $product)
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Product not in wishlist'], 404);
        }

        return response()->json([
            'message' => 'Product removed from wishlist',
            'count' => Wishlist::where('user_id', $request->user()->id)->count(),
        ]);
    }

    /**
     * Check if product is in wishlist.
     */
    public function check(Request $request, Product $product)
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->exists();

        return response()->json(['in_wishlist' => $exists]);
    }

    /**
     * Toggle product in wishlist.
     */
    public function toggle(Request $request, Product $product)
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($exists) {
            $exists->delete();
            $message = 'Product removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]);
            $message = 'Product added to wishlist';
        }

        return response()->json([
            'message' => $message,
            'in_wishlist' => !$exists,
            'count' => Wishlist::where('user_id', $request->user()->id)->count(),
        ]);
    }

    /**
     * Clear wishlist.
     */
    public function clear(Request $request)
    {
        Wishlist::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Wishlist cleared',
            'count' => 0,
        ]);
    }

    /**
     * Move all wishlist items to cart.
     */
    public function moveToCart(Request $request)
    {
        $wishlistItems = Wishlist::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($wishlistItems as $item) {
            if ($item->product && $item->product->is_active && $item->product->stock > 0) {
                // Check if already in cart
                $cartItem = CartItem::where('user_id', $request->user()->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity');
                } else {
                    CartItem::create([
                        'user_id' => $request->user()->id,
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                    ]);
                }
                $addedCount++;
            } else {
                $skippedCount++;
            }
        }

        // Clear wishlist after moving
        Wishlist::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Items moved to cart',
            'added' => $addedCount,
            'skipped' => $skippedCount,
        ]);
    }
}