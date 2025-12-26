<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyPromoCodeRequest;
use App\Services\PromoCodeService;

class PromoCodeController extends Controller
{
    /**
     * verify promo code
     *
     * endpoint to verify promo code via customer's cart
     *
     * @type POST
     *
     * @authenticated
     *
     * @group Promo Code
     *
     * @url /api/promo-code/verify
     *
     * @queryParam promo_code string required promo code
     */
    public function verifyPromoCode(PromoCodeService $promoCodeService, VerifyPromoCodeRequest $request)
    {
        try {
            return $promoCodeService->verifyPromoCode(
                code: $request->promo_code);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }
}
