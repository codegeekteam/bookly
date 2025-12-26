<?php

namespace App\Services;

use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\AttachedService;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\beginTransaction;

class CartService
{
    public function getCustomerCartWithItems(Customer $customer): CartResource
    {
        $cart = Cart::with([
                'cartItems',
                'cartItems.attachedService',
                'cartItems.attachedService.serviceProvider'
            ])
            ->where('customer_id', $customer->id)
            ->first();
        if(!$cart){
            $cart = Cart::create(['customer_id' => $customer->id]);
        }

        return new CartResource($cart);
    }

    /**
     * @throws \Exception
     */
    public function addServiceToCart(Customer $customer, int $attachedServiceId, $picked_date, array $time_slots, $delivery_type_id, int $quantity = 1,$address_id=null, $employee_id=null): CartResource
    {
        $cart = Cart::with('cartItems', 'cartItems.attachedService','cartItems.attachedService.serviceProvider')->where('customer_id', $customer->id)->first();
        if(!$cart){
            $cart = Cart::create(['customer_id' => $customer->id]);
        }
        $attachedService = AttachedService::find($attachedServiceId);
        if(!$attachedService){
            throw new \Exception(__('Service not found'));
        }

        // Validate employee if provided (for enterprise providers)
        if ($employee_id) {
            $employee = \App\Models\Employee::where('id', $employee_id)
                ->where('provider_id', $attachedService->service_provider_id)
                ->whereHas('services', function ($query) use ($attachedService) {
                    $query->where('services.id', $attachedService->service_id);
                })
                ->first();

            if (!$employee) {
                throw new \Exception(__('Employee not found or cannot perform this service'));
            }
        }

        DB::beginTransaction();
        foreach ($time_slots as $time_slot) {
            foreach ($cart->cartItems as $cartItem) {
                if ($cartItem->attachedService->service_provider_id !== $attachedService->service_provider_id) {
                    throw new \Exception(__('You can only add services from the same provider'));
                }
                if ($cartItem->attached_service_id === $attachedServiceId && $cartItem->time_slot === $time_slot && $cartItem->picked_date === $picked_date) {
                    throw new \Exception(__('Service already in cart'));
                }
                if ($cartItem->picked_date != $picked_date ) {
                    throw new \Exception(__('Please book for the same date: ' . $cartItem->picked_date));
                }
            }

            // Create a cart item for each time slot
            CartItem::create([
                'cart_id' => $cart->id,
                'attached_service_id' => $attachedServiceId,
                'quantity' => $quantity,
                'picked_date' => $picked_date,
                'time_slot' => $time_slot,
                'delivery_type_id' => $delivery_type_id,
                'address_id' => $address_id,
                'employee_id' => $employee_id,
            ]);
        }
        DB::commit();
        return  new CartResource($cart->fresh());
    }

    /**
     * @throws \Exception
     */
    public function removeServiceFromCart(Customer $customer, int $cart_item_id): CartResource
    {
        $cart = Cart::with('cartItems')->where('customer_id', $customer->id)->first();
        if(!$cart){
            throw new \Exception(__('Cart not found'));
        }

        $cartItem = $cart->cartItems()->where('id', $cart_item_id)->first();
        if(!$cartItem){
            throw new \Exception(__('Service not found in cart'));
        }

        $cartItem->delete();

        return new CartResource($cart->fresh());
    }

    /**
     * @throws \Exception
     */
    public function clearCart(Customer $customer): CartResource
    {
        $cart = Cart::with('cartItems')->where('customer_id', $customer->id)->first();
        if(!$cart){
            throw new \Exception(__('Cart not found'));
        }

        $cart->cartItems->each->delete();

        return new CartResource($cart->fresh());
    }

    /**
     * @throws \Exception
     */
    public function updateCartItemQuantity(Customer $customer, int $cart_item_id, $time_slot, $delivery_type_id, int $quantity = 1,$address_id=null): CartItemResource
    {
        $cart = Cart::where('customer_id', $customer->id)->first();
        if(!$cart){
            throw new \Exception(__('Cart not found'));
        }

        $cartItem = $cart->cartItems()->where('id', $cart_item_id)->first();
        if(!$cartItem){
            throw new \Exception(__('Service not found in cart'));
        }
        foreach ($cart->cartItems as $item) {
            if ( $item->time_slot === $time_slot && $item->attached_service_id === $cartItem->attached_service_id ) {
                throw new \Exception(__('Service already in cart'));
            }
        }
        $cartItem->quantity = $quantity;
        $cartItem->time_slot = $time_slot;
        $cartItem->delivery_type_id = $delivery_type_id??$cartItem->delivery_type_id;
        $cartItem->address_id = $address_id??$cartItem->address_id;
        $cartItem->save();

        return new CartItemResource($cartItem->fresh());
    }
}
