<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'type',
        'stock_before',
        'stock_after',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made the change.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if this was an addition.
     */
    public function getIsAdditionAttribute(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if this was a deduction.
     */
    public function getIsDeductionAttribute(): bool
    {
        return $this->quantity < 0;
    }

    /**
     * Get formatted quantity with sign.
     */
    public function getFormattedQuantityAttribute(): string
    {
        return ($this->quantity > 0 ? '+' : '') . $this->quantity;
    }
}