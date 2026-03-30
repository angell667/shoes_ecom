<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_verified_purchase',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get star rating as percentage (for CSS width).
     */
    public function getRatingPercentageAttribute(): int
    {
        return ($this->rating / 5) * 100;
    }

    /**
     * Check if review is positive (4-5 stars).
     */
    public function getIsPositiveAttribute(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Check if review is negative (1-2 stars).
     */
    public function getIsNegativeAttribute(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Mark review as helpful.
     */
    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Approve the review.
     */
    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    /**
     * Reject the review.
     */
    public function reject(): void
    {
        $this->update(['is_approved' => false]);
    }
}