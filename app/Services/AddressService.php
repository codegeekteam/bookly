<?php

namespace App\Services;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;

class AddressService
{
    public function index(User $user)
    {

        if ($user->customer) {
            return AddressResource::collection($user->customer->addresses);
        }
        if ($user->serviceProvider) {
            return AddressResource::collection($user->serviceProvider->addresses);
        }

        throw new \Exception(__('User is not a customer or service provider'));
    }

    public function create(User $user, AddressRequest $request): AddressResource
    {
        $account = $user->customer ?? $user->serviceProvider;

        $hasAddresses = $account->addresses()->exists();

        // If no addresses exist, set the new address as the default
        $validatedData = $request->validated();
        $validatedData['is_default'] = !$hasAddresses;

        // If is_default is true, reset any previous default address for the user
        if ($request->is_default && $hasAddresses) {
            $account->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        // Create the address

        $address = $account->addresses()->create($validatedData);


        return new AddressResource($address);
    }

    public function update(User $user, Request $request)
    {
        $address = '';
        $account = $user->customer ?? $user->serviceProvider;
        // If is_default is true, reset any previous default address for the user
        if ($request->is_default) {
            $account->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address = $account->addresses()->find($request->address_id);
        $address->update([
            'address_name' => $request->address_name ?? $address->address_name,
            'latitude' => $request->latitude ?? $address->latitude,
            'longitude' => $request->longitude ?? $address->longitude,
            'address_details' => $request->address_details ?? $address->address_details,
            'is_default' => $request->is_default ?? $address->is_default,
        ]);

        return new AddressResource($address);
    }

    /**
     * @throws \Exception
     */
    public function delete(User $user, $address_id): void
    {
        $address = null;
        $account = $user->customer ?? $user->serviceProvider;

        $address = $account->addresses()->find($address_id);

        if ($address) {

            if ($account->addresses()->count() <= 1) {
                // Prevent deletion if it's the only address
                throw new \Exception(__('you must have at least one address'));
            }

            // If the address is the default, find another address to set as default before deletion
            if ($address->is_default) {
                $newDefault = $account->addresses()->where('id', '!=', $address_id)->first();

                // If a new default address is found, set it as default
                $newDefault?->update(['is_default' => true]);
            }

            // Delete the address
            $address->delete();
        }
    }

    public function providerAddress($provider_id)
    {
        $address = Address::where('addressable_id', $provider_id)->where('addressable_type', ServiceProvider::class)->first();
        if ($address) {           
            return new AddressResource($address);
        }

        throw new \Exception(__('no data found'));
    }
}