<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyDiscountCustomer extends Model
{

    protected $fillable = [
        'loyalty_discount_id',
        'customer_id',
        'points',
        'discount_type_id',
        'discount_amount',
        'maximum_discount',
        'is_used',
        'minimum_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function loyaltyDiscount()
    {
        return $this->belongsTo(LoyaltyDiscount::class,'loyalty_discount_id');
    }

    public function discountType()
    {
        return $this->belongsTo(DiscountType::class);
    }

}
