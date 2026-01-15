<?php

namespace App\Actions\LoyaltyPoints\Mutations;

use App\Models\LoyaltyDiscount;
use App\Models\LoyaltyDiscountCustomer;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CheckLoyaltyDiscountMutation
{
    public function handle($id,$customer)
    {
        $LoyaltyDiscount = LoyaltyDiscount::where('id', $id)->first();
        if(!$LoyaltyDiscount)
        {
            throw ValidationException::withMessages([
                'loyalty_discount' => 'Invalid loyalty discount',
            ]);
        }
        if($LoyaltyDiscount->start_date > Carbon::now())
        {
            throw ValidationException::withMessages([
                'loyalty_discount' => 'loyalty discount not started yet',
            ]);
        }
        if($LoyaltyDiscount->end_date < Carbon::now())
        {
            throw ValidationException::withMessages([
                'loyalty_discount' => 'this loyalty discount expired',
            ]);
        }

        $is_redeemed=LoyaltyDiscountCustomer::where('customer_id',$customer->id)->where('loyalty_discount_id',$LoyaltyDiscount->id)->exists();
        if($is_redeemed)
        {
            throw ValidationException::withMessages([
                'loyalty_discount' => 'you redeemed this loyalty discount before',
            ]);
        }

        return $LoyaltyDiscount;

    }
}
