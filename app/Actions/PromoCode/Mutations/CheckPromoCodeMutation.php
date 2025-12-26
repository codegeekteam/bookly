<?php

namespace App\Actions\PromoCode\Mutations;

use App\Models\Appointment;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CheckPromoCodeMutation
{
    public function handle($code,$customer)
    {
        $PromoCode = PromoCode::where('code', $code)->first();
        if(!$PromoCode)
        {
            throw ValidationException::withMessages([
                'promo_code' => 'Invalid promo code',
            ]);
        }
        if($PromoCode->start_date > Carbon::now())
        {
            throw ValidationException::withMessages([
                'promo_code' => 'promo code not started yet',
            ]);
        }
        if($PromoCode->end_date < Carbon::now())
        {
            throw ValidationException::withMessages([
                'promo_code' => 'this promo code expired',
            ]);
        }
        if($PromoCode->is_for_services == 0)
        {
            throw ValidationException::withMessages([
                'promo_code' => 'this promo code not available for services',
            ]);
        }
        if($PromoCode->count_of_redeems>=$PromoCode->maximum_redeems)
        {
            throw ValidationException::withMessages([
                'promo_code' => 'this promo code extend number of redeems',
            ]);
        }
        $count_of_appointments=Appointment::where('customer_id',$customer->id)->where('promo_code_id',$PromoCode->id)->whereNotIn('status_id',['3','4'])->count();
        if($count_of_appointments>0)
        {
            throw ValidationException::withMessages([
                'promo_code' => 'you used this promo code before',
            ]);
        }

    }
}
