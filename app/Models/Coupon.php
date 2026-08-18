<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'type',
        'discount_type',
        'discount_value',
        'course_id',
        'min_order_amount',
        'max_uses_per_user',
        'max_uses',
        'uses',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_order_amount' => 'float',
        'max_uses_per_user' => 'integer',
        'max_uses' => 'integer',
        'uses' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }

    /**
     * Check if coupon is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses > 0 && $this->uses >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
