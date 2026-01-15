<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * get addresses
     *
     * endpoint to retrieve logged in user addresses (customer, service provider)
     *
     * @type GET
     *
     * @url /api/addresses
     *
     * @authenticated
     *
     * @group Address
     *
     * @response { "data": [ { "id": 2, "address_name": "264 mahmoudia canal st.", "latitude": "2.50", "longitude": "2.50", "address_details": "264 mahmoudia canal st.", "created_at": "2023-09-18T12:26:18.000000Z", "updated_at": "2023-09-18T12:26:18.000000Z" }, { "id": 3, "address_name": "maadi", "latitude": "2.60", "longitude": "2.60", "address_details": "maadi", "created_at": "2023-09-18T12:29:56.000000Z", "updated_at": "2023-09-18T12:29:56.000000Z" }, { "id": 6, "address_name": "maadi", "latitude": "25.50", "longitude": "25.50", "address_details": "behind a shop", "created_at": "2023-09-18T12:41:56.000000Z", "updated_at": "2023-09-18T12:41:56.000000Z" } ] }
     */
    public function index(AddressService $addressService)
    {
        try {
            return $addressService->index(auth()->user());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * create address
     *
     *
     *
     *endpoint to create address for freelancers,organizations and customers
     *
     * @type POST
     *
     * @url api/addresses
     *
     * @group Address
     *
     * @authenticated
     *
     * @bodyParam address_name string required
     * @bodyParam latitude string required
     * @bodyParam longitude string required
     * @bodyParam address_details string nullable
     * @bodyParam is_default boolean
     * @response { "data": { "id": 6, "address_name": "maadi", "latitude": "25.5", "longitude": "25.5", "address_details": "behind a shop", "created_at": "2023-09-18T12:41:56.000000Z", "updated_at": "2023-09-18T12:41:56.000000Z" } }
     *
     */
    public function create(AddressService $addressService, AddressRequest $request)
    {
        try {
            return $addressService->create(auth()->user(), $request);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    /**
     * update address
     *
     *
     *
     * endpoint to update customer/provider address
     *
     * @bodyParam address_id int required
     * @bodyParam address_name string
     * @bodyParam latitude string
     * @bodyParam longitude string
     * @bodyParam address_details string
     * @bodyParam is_default boolean
     *
     * @url /api/addresses/update
     *
     * @type POST
     *
     * @authenticated
     *
     * @group Address
     *
     */
    public function update(Request $request, AddressService $addressService)
    {
        try {
            return $addressService->update(user: auth()->user(), request: $request);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * delete address
     *
     *
     *
     * endpoint to delete customer/provider address
     *
     * @url /api/addresses/{id}
     *
     * @type DELETE
     *
     * @authenticated
     *
     * @group Address
     *
     * @response 200 {
     *  "message": "address deleted successfully"
     * }
     */
    public function destroy($id, AddressService $addressService): ?\Illuminate\Http\JsonResponse
    {
        try {
            $addressService->delete(auth()->user(), $id);

            return response()->json(['message' => 'address deleted successfully'], 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * get service provider address
     *
     * endpoint to retrieve given service provider  address
     *
     * @type GET
     *
     * @url /api/addresses/{provider_id}/address
     *
     * @authenticated
     *
     * @group Address
     *
     * @param  provider_id integer required The id of the provider. Example: 1
     *
     * @response { "data": { "id": 2, "address_name": "264 mahmoudia canal st.", "latitude": "2.50", "longitude": "2.50", "address_details": "264 mahmoudia canal st.", "created_at": "2023-09-18T12:26:18.000000Z", "updated_at": "2023-09-18T12:26:18.000000Z" }, { "id": 3, "address_name": "maadi", "latitude": "2.60", "longitude": "2.60", "address_details": "maadi", "created_at": "2023-09-18T12:29:56.000000Z", "updated_at": "2023-09-18T12:29:56.000000Z" } }
     */
    public function serviceProviderAddress( int $provider_id,AddressService $addressService)
    {
        try {
            return $addressService->providerAddress($provider_id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

}
