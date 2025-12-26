<?php

namespace App\Actions\PromoCode\Mutations;

use App\Models\PromoCode;

class PromoCodeCalculationsMutation
{
    public function handle(PromoCode $promoCode,$total)
    {
        $discount=0;
        //fixed amount
        if($promoCode->discount_type_id == 1){
            $discount=$promoCode->discount_amount;
        }
        //percentage
        if($promoCode->discount_type_id == 2){
            $amount=($promoCode->discount_amount/100)*$total;
            $discount=$amount;
            if($amount>$promoCode->maximum_discount){
                $discount=$promoCode->maximum_discount;
            }
        }
        //if discount greater than total discount auto will be the total
        return $discount > $total?$total:$discount;
    }
}
