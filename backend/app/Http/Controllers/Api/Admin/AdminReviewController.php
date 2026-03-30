<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Get all reviews with filters.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name,email', 'product:id,name,slug,image']);

        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'reviews' => $reviews,
            'stats' => [
                'total' => Review::count(),
                'pending' => Review::where('is_approved', false)->count(),
                'approved' => Review::where('is_approved', true)->count(),
                'average_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
            ],
        ]);
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review)
    {
        $review->approve();

        return response()->json([
            'message' => 'Review approved successfully',
            'review' => $review->fresh()->load(['user:id,name', 'product:id,name']),
        ]);
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review)
    {
        $review->reject();

        return response()->json([
            'message' => 'Review rejected successfully',
            'review' => $review->fresh()->load(['user:id,name', 'product:id,name']),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}