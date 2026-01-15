<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGiftCardRequest;
use App\Http\Requests\RedeemGiftCardRequest;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    /**
     * get all gifts
     *
     *
     * @type GET
     *
     * endpoint to get all gifts that belong to the authenticated user
     *
     * @url /api/gift-cards
     *
     * @authenticated
     *
     * @group Gift Cards
     *
     *
     * @apiResourceCollection App\Http\Resources\GiftCardCollection
     * @apiResourceModel App\Models\GiftCard paginate=1
     */
    public function index(GiftCardService $giftCardService)
    {
        try {
            return $giftCardService->index(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * show a gift
     *
     * @type GET
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards/{id}
     *
     * @authenticated
     *
     *
     *
     * @apiResource App\Http\Resources\GiftCardResource
     * @apiResourceModel App\Models\GiftCard
     */
    public function show(GiftCardService $giftCardService, $id)
    {
        try {
            return $giftCardService->show(auth()->user(), $id);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     *
     * create gift card
     *
     * @type POST
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards
     *
     * @authenticated
     *
     * endpoint to create a gift card
     *
     * @bodyParam recipient_name string required The name of the recipient. Example: M. Ashraf
     * @bodyParam amount number required The amount of the Gift Card. Example: 10
     * @bodyParam recipient_phone_number string required The phone number of the recipient. Example: 0778559755
     *
     * @response 200 { "id": 11, "code": "bkly-s3KaA", "user": "rem", "amount": "100", "recipient_name": "M. Ashraf", "recipient_email": "mashraf@gmail.com", "recipient_phone_number": "0778559755", "is_used": false, "used_by": null, "appointment_id": null, "created_at": "2023-12-12T16:28:42.000000Z", "updated_at": "2023-12-12T16:28:42.000000Z" }
     */
    public function create(GiftCardService $giftCardService, CreateGiftCardRequest $request)
    {
        try {
            return $giftCardService->create(
                user: auth()->user(),
                gift_card_theme_id: $request->gift_card_theme_id,
                recipient_name: $request->recipient_name,
                recipient_phone_number: $request->recipient_phone_number,
                 amount: $request->amount
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get used gift cards
     *
     * @type GET
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards/used/get
     *
     * @authenticated
     *
     * Endpoint to get all gift cards used by the authenticated user.
     *
     * @apiResourceCollection App\Http\Resources\GiftCardResource
     * @apiResourceModel App\Models\GiftCard paginate=1
     */
    public function usedGiftCards(GiftCardService $giftCardService)
    {
        try {
            return $giftCardService->usedGiftCards(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get received gift cards
     *
     * @type GET
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards/received/get
     *
     * @authenticated
     *
     * Endpoint to get all gift cards received by the authenticated user.
     *
     * @apiResourceCollection App\Http\Resources\GiftCardResource
     * @apiResourceModel App\Models\GiftCard paginate=1
     */
    public function receivedGiftCards(GiftCardService $giftCardService)
    {
        try {
            return $giftCardService->receivedGiftCards(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get  gift card themes
     *
     * @type GET
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards/themes/get
     *
     * @authenticated
     *
     * Endpoint to get all gift card themes.
     *
     * @apiResource App\Http\Resources\GiftCardThemeResource
     * @apiResourceModel App\Models\GiftCardTheme
     */

    public function giftCardThemes(GiftCardService $giftCardService)
    {
        try {
            return $giftCardService->giftCardThemes();

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     *
     * redeem gift card
     *
     * @type POST
     *
     * @group Gift Cards
     *
     * @url /api/gift-cards/redeem
     *
     * @authenticated
     *
     * endpoint to create a gift card
     *
     * @bodyParam code string required The code of the gift card. Example: fffGHJ
     *
     * @response 200 {
     *  "message": "code"
     * }
     */
    public function redeem(GiftCardService $giftCardService, RedeemGiftCardRequest $request)
    {
        try {
            $giftCard = $giftCardService->redeem(user: auth()->user(), code: $request->code);
            return response()->json(['message' => __('gift card code redeemed successfully')], 200);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
