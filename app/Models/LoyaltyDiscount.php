<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyDiscount extends Model
{
    use HasFactory;

    protected $fillable = [ 'points', 'discount_type_id', 'discount_amount', 'maximum_discount', 'start_date', 'end_date','minimum_amount'];

    public function discountType()
    {
        return $this->belongsTo(DiscountType::class);
    }

    public function loyaltyDiscountCustomers()
    {
        return $this->hasMany(LoyaltyDiscountCustomer::class, 'loyalty_discount_id');
    }

    // Accessor for 'is_redeemed' - check if the authenticated user has redeemed the discount
    public function getIsRedeemedAttribute()
    {
        // Get the currently authenticated user's ID
        $user = auth('api')->user();
        if($user){
            if($user->customer){
                // Check if there is a corresponding LoyaltyDiscountCustomer record for the user and discount
                return $this->loyaltyDiscountCustomers->contains('customer_id', $user->customer->id);
            }
        }
        return false;


    }

}
