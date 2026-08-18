<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = 'coupon_usages';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'discount_amount',
    ];

    protected $casts = [
        'coupon_id' => 'integer',
        'customer_id' => 'integer',
        'order_id' => 'integer',
        'discount_amount' => 'float',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
