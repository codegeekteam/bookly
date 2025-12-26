<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGiftCardRequest;
use App\Http\Requests\RedeemLoyaltyPointDiscountRequest;
use App\Http\Requests\VerifyLoyaltyDiscountRequest;
use App\Http\Resources\PointTransactionResource;
use App\Services\LoyaltyPointsService;
use Illuminate\Http\Request;

class LoyaltyPointsController extends Controller
{
    /**
     * get loyalty points transactions
     *
     *
     * @type GET
     *
     * endpoint to get all loyalty points transactions that belong to the authenticated user
     *
     * @url /api/loyalty-points/transactions
     *
     * @authenticated
     *
     * @group Loyalty Points
     *
     * @queryParam type string Optional. Filter transactions by type. Example: in, out
     *
     * @apiResourceCollection App\Http\Resources\PointTransactionResource
     * @apiResourceModel App\Models\PointTransaction paginate=1
     */
    public function transactions(LoyaltyPointsService $LoyaltyPointsService , Request $request)
    {
        try {
            $transactions= $LoyaltyPointsService->transactions(auth()->user(),$request);
            return PointTransactionResource::collection($transactions);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * get Loyalty Points Discounts By Id
     *
     * @type GET
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/show-by-id/{id}
     *
     * @authenticated
     *
     * @apiResource App\Http\Resources\LoyaltyDiscountResource
     * @apiResourceModel App\Models\LoyaltyDiscount
     */
    public function show(LoyaltyPointsService $LoyaltyPointsService, $id)
    {
        try {
            return $LoyaltyPointsService->show(auth()->user(), $id);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     *
     * create loyalty point discount
     *
     * @type POST
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points
     *
     * @authenticated
     *
     * endpoint to create a loyalty point discount
     *
     * @bodyParam recipient_name string required The name of the recipient. Example: M. Ashraf
     * @bodyParam amount number required The amount of the loyalty point discount. Example: 10
     * @bodyParam recipient_phone_number string required The phone number of the recipient. Example: 0778559755
     *
     * @response 200 { "id": 11, "code": "bkly-s3KaA", "user": "rem", "amount": "100", "recipient_name": "M. Ashraf", "recipient_email": "mashraf@gmail.com", "recipient_phone_number": "0778559755", "is_used": false, "used_by": null, "appointment_id": null, "created_at": "2023-12-12T16:28:42.000000Z", "updated_at": "2023-12-12T16:28:42.000000Z" }
     */
    public function create(LoyaltyPointsService $LoyaltyPointsService, CreateGiftCardRequest $request)
    {
        try {
            return $LoyaltyPointsService->create(
                user: auth()->user(),
                gift_card_theme_id: $request->gift_card_theme_id,
                recipient_name: $request->recipient_name,
                recipient_email: $request->recipient_email,
                recipient_phone_number: $request->recipient_phone_number,
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Get redeemed Loyalty Points
     *
     * @type GET
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/redeemed
     *
     * @authenticated
     *
     * Endpoint to get all Loyalty Points redeemed by the authenticated user.
     *
     * @apiResourceCollection App\Http\Resources\LoyaltyDiscountCustomerResource
     * @apiResourceModel App\Models\LoyaltyDiscountCustomer paginate=1
     */
    public function redeemedLoyaltyDiscounts(LoyaltyPointsService $LoyaltyPointsService)
    {
        try {
            return $LoyaltyPointsService->redeemedLoyaltyDiscounts(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get available for use redeemed Loyalty Points
     *
     * @type GET
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/redeemed-available
     *
     * @authenticated
     *
     * Endpoint to get all Loyalty Points redeemed by the authenticated user and available for use.
     *
     * @apiResourceCollection App\Http\Resources\LoyaltyDiscountCustomerResource
     * @apiResourceModel App\Models\LoyaltyDiscountCustomer
     */
    public function getAvailableRedeemedLoyaltyDiscounts(LoyaltyPointsService $LoyaltyPointsService)
    {
        try {
            return $LoyaltyPointsService->availableForUseRedeemedLoyaltyDiscounts(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get  loyalty discounts
     *
     * @type GET
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/get
     *
     * @authenticated
     *
     * Endpoint to get all loyalty point discounts .
     *
     * @apiResource App\Http\Resources\LoyaltyDiscountResource
     * @apiResourceModel App\Models\LoyaltyDiscount
     */

    public function loyaltyDiscounts(LoyaltyPointsService $LoyaltyPointsService)
    {
        try {
            return $LoyaltyPointsService->loyaltyDiscounts();

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     *
     * redeem loyalty point discount
     *
     * @type POST
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/redeem
     *
     * @authenticated
     *
     * endpoint to create a loyalty point discount
     *
     * @bodyParam id int required The id of the loyalty point discount. Example: 1
     *
     * @response 200 {
     *  "message": "loyalty point discount redeemed successfully"
     * }
     */
    public function redeem(LoyaltyPointsService $LoyaltyPointsService, RedeemLoyaltyPointDiscountRequest $request)
    {
        try {
            $loyaltyDiscount = $LoyaltyPointsService->redeem(user: auth()->user(), id: $request->id);
            return response()->json(['message' => __('loyalty discount redeemed successfully')], 200);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * verify loyalty discount
     *
     * endpoint to verify loyalty discount via customer's cart
     *
     * @type POST
     *
     * @authenticated
     *
     * @group Loyalty Points
     *
     * @url /api/loyalty-points/discounts/verify
     *
     * @queryParam id int required The id of the loyalty point discount from redeemed loyalty discounts. Example: 1\
     *
     * @response 200 {"id": 3,"discount_type": "fixed","discount_amount": "10.00 SAR ","total_after_discount": 190}
     */
    public function verifyLoyaltyDiscount(LoyaltyPointsService $LoyaltyPointsService, VerifyLoyaltyDiscountRequest $request)
    {
        try {
            return $LoyaltyPointsService->verifyLoyaltyDiscount(user: auth()->user(), id: $request->id);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }
}
