<?php

namespace App\Services;

use App\Http\Resources\FavouriteCollection;
use App\Models\AttachedService;
use App\Models\Customer;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class FavouriteService
{
    public function index(Customer $customer): FavouriteCollection
    {
        $favourites = $customer->favourites()->whereHas('service', function (Builder $query) {
            $query->whereHas('service', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('serviceProvider', function (Builder $query){
                $query->where('is_active', true);
            });
        })->get();

        return new FavouriteCollection($favourites);
    }

    /**
     * @throws Exception
     */
    public function store(Customer $customer, int $service_id): \Illuminate\Http\JsonResponse
    {
        $is_favourite_exists = AttachedService::find($service_id);

        if (! $is_favourite_exists) {
            throw new Exception('This service does not exist.');
        }

        $is_customer_has_service = $customer->favourites()->where('service_id', $service_id)->exists();

        if ($is_customer_has_service) {
            throw new Exception('This service is already in your favourites.');
        }

        $customer->favourites()->create([
            'service_id' => $service_id,
        ]);

        return response()->json([
            'message' => 'Service added to favourites.',
        ]);
    }

    /**
     * @throws Exception
     */
    public function delete(Customer $customer, $service_id): \Illuminate\Http\JsonResponse
    {

        $favourite = $customer->favourites()->where('service_id', $service_id)->first();

        if (! $favourite) {
            throw new Exception('This service is not in your favourites.');
        }

        $favourite->delete();

        return response()->json([
            'message' => 'Service removed from favourites.',
        ]);
    }
}
