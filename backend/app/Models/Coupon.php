<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if coupon is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if order meets minimum requirement.
     */
    public function meetsMinimumOrder(float $subtotal): bool
    {
        if (!$this->minimum_order) {
            return true;
        }

        return $subtotal >= $this->minimum_order;
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->meetsMinimumOrder($subtotal)) {
            return 0;
        }

        $discount = 0;

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
        } else {
            $discount = $this->value;
        }

        // Apply maximum discount limit
        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        // Don't allow discount more than subtotal
        return min($discount, $subtotal);
    }

    /**
     * Mark coupon as used.
     */
    public function markAsUsed(): void
    {
        $this->increment('used_count');
    }

    /**
     * Scope to active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Find a valid coupon by code.
     */
    public static function findValidCoupon(string $code): ?self
    {
        return static::where('code', strtoupper($code))
            ->active()
            ->first();
    }
}