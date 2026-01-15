<?php

namespace App\Actions\LoyaltyPoints\Mutations;

use App\Models\LoyaltyDiscountCustomer;

class LoyaltyDiscountCalculationsMutation
{
    public function handle(LoyaltyDiscountCustomer $loyaltyDiscount,$total)
    {
        $discount=0;
        //fixed amount
        if($loyaltyDiscount->discount_type_id == 1){
            $discount=$loyaltyDiscount->discount_amount;
        }
        //percentage
        if($loyaltyDiscount->discount_type_id == 2){
            $amount=($loyaltyDiscount->discount_amount/100)*$total;
            $discount=$amount;
            if($amount>$loyaltyDiscount->maximum_discount){
                $discount=$loyaltyDiscount->maximum_discount;
            }
        }
        //if discount greater than total discount auto will be the total
        return $discount > $total?$total:$discount;
    }
}
