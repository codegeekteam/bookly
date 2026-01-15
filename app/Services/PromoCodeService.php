<?php

namespace App\Services;

use App\Actions\PromoCode\Mutations\CheckPromoCodeMutation;
use App\Actions\PromoCode\Mutations\PromoCodeCalculationsMutation;
use App\Models\PromoCode;

class PromoCodeService
{
    public function verifyPromoCode(string $code): array
    {
        $promo_code = PromoCode::where('code', $code)->first();

        if (! $promo_code) {
            throw new \Exception(__('Promo code not found'));
        }


        $customer = auth()->user()?->customer;

        if ($customer === null) {
            throw new \Exception(__('user should be customer'));
        }
        //check promo code
        (new CheckPromoCodeMutation())->handle($code, $customer);
        $customer_cart = auth()->user()->customer?->cart;

        if ($customer_cart->cartItems->isEmpty()) {
            throw new \Exception(__('Cart is empty'));
        }

        $customer_total_cart = $customer_cart->total;
        $customer_total_amount_due = $customer_cart->amount_due;
        $cart_services = $customer_cart->cartItems->map(function ($cartItem) {
            return $cartItem->attachedService->service_id;
        });

        foreach ($cart_services as $service) {
            if (! $promo_code->services->contains($service)) {
                throw new \Exception(__('Promo code is not for these services'));
            }
        }

         //calculate promo code
        $discount=(new PromoCodeCalculationsMutation())->handle($promo_code,$customer_total_cart);
        $discount_amount =  $discount.' SAR ';

        $total_after_discount = $customer_total_cart-$discount;
        $amount_due_after_discount =max(0, $customer_total_amount_due - $discount);

        return [
            'code' => $promo_code->code,
            'discount_type' => $promo_code->discountType->title,
            'discount_amount' => $discount_amount,
            'total_after_discount' => $total_after_discount,
            'amount_due_after_discount' => $amount_due_after_discount,
        ];

    }
}
