<?php

namespace App\Actions\LoyaltyPoints\Mutations;

use App\Models\LoyaltyDiscountCustomer;
use Illuminate\Validation\ValidationException;

class CheckLoyaltyDiscountUsageMutation
{
    public function handle($id,$customer)
    {

        $discount=LoyaltyDiscountCustomer::where('id',$id)->where('customer_id',$customer->id)->first();
        if(!$discount){
            throw ValidationException::withMessages([
                'loyalty_discount' => 'loyalty discount not found',
            ]);
        }
        if($discount->is_used==1)
        {
            throw ValidationException::withMessages([
                'loyalty_discount' => 'you used this loyalty discount before',
            ]);
        }

        return $discount;

    }
}
