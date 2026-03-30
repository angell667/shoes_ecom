<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get reviews for a product.
     */
    public function index(Product $product)
    {
        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->orderBy('helpful_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'reviews' => $reviews,
            'summary' => [
                'average_rating' => $product->average_rating,
                'total_reviews' => $product->total_reviews,
                'distribution' => $product->rating_distribution,
            ],
        ]);
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Check if user has already reviewed this product
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this product'], 422);
        }

        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', $request->user()->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve for now, can be changed to false for moderation
            'is_verified_purchase' => $hasPurchased,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('user:id,name'),
        ], 201);
    }

    /**
     * Update a review.
     */
    public function update(Request $request, Product $product, Review $review)
    {
        // Ensure the review belongs to the user
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update($request->only(['rating', 'title', 'comment']));

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->fresh()->load('user:id,name'),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, Product $product, Review $review)
    {
        // Ensure the review belongs to the user or user is admin
        if ($review->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(Request $request, Review $review)
    {
        $review->markAsHelpful();

        return response()->json([
            'message' => 'Review marked as helpful',
            'helpful_count' => $review->helpful_count,
        ]);
    }

    /**
     * Get user's reviews.
     */
    public function myReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('product:id,name,slug,image')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    /**
     * Get pending reviews (admin only).
     */
    public function pending(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reviews = Review::where('is_approved', false)
            ->with(['user:id,name', 'product:id,name,slug,image'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Approve a review (admin only).
     */
    public function approve(Request $request, Review $review)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->approve();

        return response()->json(['message' => 'Review approved successfully']);
    }

    /**
     * Reject a review (admin only).
     */
    public function reject(Request $request, Review $review)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->reject();

        return response()->json(['message' => 'Review rejected successfully']);
    }
}