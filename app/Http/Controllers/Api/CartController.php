<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddServiceToCartRequest;
use App\Http\Requests\ChangeCartItemQuantityRequest;
use App\Http\Requests\UpdateServiceFromCartRequest;
use App\Services\CartService;

class CartController extends Controller
{
    public function __construct(protected readonly CartService $cartService)
    {
    }

    /**
     * Get cart with items
     * @group Cart
     * @authenticated
     *
     * @response 200 {
     *     "data": {
     *         "total": 123,
     *         "items": [
     *             {
     *                 "attached_service_id": 2,
     *                 "quantity": 1,
     *                 "title": "enim",
     *                 "provider_name": "in",
     *                 "provider_image": "http://127.0.0.1:8000/assets/default.jpg",
     *                 "provider_type": "enterprise",
     *                 "rating": "0.0000",
     *                 "is_favorite": false,
     *                 "price": 123,
     *                 "total": 123
     *             }
     *         ]
     *     }
     * }
     * @return \App\Http\Resources\CartResource
     */
    public function index()
    {
        return $this->cartService->getCustomerCartWithItems(auth()->user()->customer);
    }

    /**
     * Add service to cart
     * @group Cart
     * @authenticated
     *
     * @bodyParam number_of_beneficiaries int The quantity of the service. Example: 2
     * @bodyParam picked_date date required The date the service will be picked. Example: 2021-12-12
     * @bodyParam time_slot string required The time slot the service will be picked. Example: 10:00 AM
     * @bodyParam delivery_type_id int required The delivery type id. Example: 1
     * @bodyParam attached_service_id int required The attached service id. Example: 1
     *
     * @response 200 {"data":{"attached_service_id":2,"quantity":2,"title":"enim","provider_name":"in","provider_image":"http:\/\/127.0.0.1:8000\/assets\/default.jpg","provider_type":"enterprise","rating":"0.0000","is_favorite":false,"price":123,"total":246}}
     */
    public function store(AddServiceToCartRequest $request)
    {

        try {
            return response()->json( $this->cartService->addServiceToCart(
                auth()->user()->customer,
                $request->input('attached_service_id'),
                $request->input('picked_date'),
                $request->input('time_slot'),
                $request->input('delivery_type_id'),
                $request->input('number_of_beneficiaries', 1),
                $request->input('address_id'),
                $request->input('employee_id'),
            ), 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Update cart item
     * @group Cart
     * @authenticated
     *
     * @bodyParam number_of_beneficiaries int The quantity of the service. Example: 2
     * @bodyParam picked_date date required The date the service will be picked. Example: 2021-12-12
     * @bodyParam time_slot string required The time slot the service will be picked. Example: 10:00 AM
     * @bodyParam delivery_type_id int required The delivery type id. Example: 1
     *
     * @response 200 {"data":{"attached_service_id":2,"quantity":1,"title":"enim","provider_name":"in","provider_image":"http:\/\/127.0.0.1:8000\/assets\/default.jpg","provider_type":"enterprise","rating":"0.0000","is_favorite":false,"price":123,"total":123}}
     */
    public function update(int $cart_item_id, UpdateServiceFromCartRequest $request){
        try {
            return $this->cartService->updateCartItemQuantity(
                auth()->user()->customer,
                $cart_item_id,
                $request->input('time_slot'),
                $request->input('delivery_type_id'),
                $request->input('number_of_beneficiaries', 1),
                $request->input('address_id'),
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Remove service from cart
     * @group Cart
     * @authenticated
     *
     *
     * @response {"data":{"total":0,"items":[]}}
     */
    public function destroy(int $cart_item_id)
    {
        try {
            return $this->cartService->removeServiceFromCart(auth()->user()->customer, $cart_item_id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Clear cart
     * @group Cart
     * @authenticated
     *
     * @response {"data":{"total":0,"items":[]}}
     */
    public function clearCart()
    {
        try {
            return $this->cartService->clearCart(auth()->user()->customer);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
