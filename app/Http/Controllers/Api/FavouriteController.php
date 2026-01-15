<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteCollection;
use App\Services\FavouriteService;
use Filament\Forms\Get;
use Illuminate\Http\JsonResponse;

class FavouriteController extends Controller
{
    /**
     * get favourite services
     *
     * endpoint to get all favourite services for the authenticated customer
     *
     * @group Favourites
     *
     * @return FavouriteCollection|JsonResponse
     *
     * @url api/customers/favourites
     *
     * @authenticated
     *
     * @response 200 [ { "id": 3, "service": { "id": 1, "service": "aperiam", "service_id": 1, "service_description": "Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque commodo eros a enim. Cras sagittis. Morbi mollis tellus ac sapien. Nam at tortor in tellus interdum sagittis.", "currency": "SAR", "service_image": "http://localhost:8000/assets/default.jpg", "service_provider": "harum", "service_provider_type": "enterprise", "service_provider_image": "http://localhost:8000/assets/default.jpg", "rating": 2, "is_favourite": true, "price": 200, "min_price": 200, "max_price": 200, "service_provider_id": 1, "average_rate": 2, "my_place": false, "customer_place": false }, "provider_name": "harum", "provider_type": "enterprise", "provider_profile_picture": "http://localhost:8000/assets/default.jpg" } ]
     */
    public function index(FavouriteService $favouriteService)
    {

        try {
            return $favouriteService->index(customer: auth()->user()->customer);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }

    /**
     * store favourite service
     *
     *
     * endpoint to store a new favourite service for the authenticated customer
     * the service can only be added to the customer's favourites once
     *
     * @group Favourites
     *
     * @return JsonResponse
     *
     * @authenticated
     *
     * @url api/customers/favourites/{service_id}
     *
     * @urlParam  service_id integer required The id of the attached service to be added to the customer's favourites
     *
     * @response 200  { "message": "Service added to favourites." }
     * @response 400 { "message": "This service is already in your favourites." }
     */
    public function store(FavouriteService $favouriteService, $service_id)
    {
        try {
            return $favouriteService->store(customer: auth()->user()->customer, service_id: $service_id);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * Delete favourite service
     *
     *
     * endpoint to delete a favourite service for the authenticated customer
     *
     * @group Favourites
     *
     * @return JsonResponse
     *
     * @authenticated
     *
     * @url api/customers/favourites/{service_id}
     *
     * @bodyParam $service_id integer required The id of the attached service that the customer added to his favourites
     *
     * @response 200 { 'message' : 'Service removed from favourites.' }
     * @response 400 { "message": "This service is not in your favourites." }
     */
    public function delete(FavouriteService $favouriteService, $service_id)
    {
        try {
            return $favouriteService->delete(customer: auth()->user()->customer, service_id: $service_id);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
