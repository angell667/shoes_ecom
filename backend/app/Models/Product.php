<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    
    protected $appends = ['effective_price', 'discount_percentage'];

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'sale_price',
        'image', 'images', 'brand', 'sku', 'stock', 'is_featured', 'is_active'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->approved();
    }

    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->price > $this->sale_price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Get average rating for the product.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count.
     */
    public function getTotalReviewsAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get rating distribution (1-5 stars).
     */
    public function getRatingDistributionAttribute(): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->approvedReviews()->where('rating', $i)->count();
        }
        return $distribution;
    }

    /**
     * Check if product has any reviews.
     */
    public function getHasReviewsAttribute(): bool
    {
        return $this->total_reviews > 0;
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Get low stock threshold.
     */
    public function getLowStockThresholdAttribute(): int
    {
        return 5; // Can be made configurable
    }

    /**
     * Check if product is low on stock.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_threshold;
    }

    /**
     * Check if product is out of stock.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }
}
