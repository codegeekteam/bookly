<?php

namespace App\Services;

use App\Actions\LoyaltyPoints\Mutations\CheckLoyaltyDiscountMutation;
use App\Actions\LoyaltyPoints\Mutations\CheckLoyaltyDiscountUsageMutation;
use App\Actions\LoyaltyPoints\Mutations\CreatePointTransactionMutation;

use App\Actions\LoyaltyPoints\Mutations\LoyaltyDiscountCalculationsMutation;
use App\Http\Resources\LoyaltyDiscountCustomerResource;
use App\Http\Resources\LoyaltyDiscountResource;

use App\Models\LoyaltyDiscount;
use App\Models\LoyaltyDiscountCustomer;
use App\Models\User;
use App\Models\Enums\TransactionType;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class LoyaltyPointsService
{
    /**
     * @throws Exception
     */

    public function transactions(User $user, $request)
    {
        if (!$user->customer) {
            throw new Exception('no data found');
        }

        // Access the histories relationship with conditional filters
        $query = $user->customer->pointTransactions();

        // Apply date filters conditionally
        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', '=', $request->type);
        });

        return $query->orderByDesc('created_at')->paginate(10);
    }

    public function show(User $user, $id): LoyaltyDiscountResource
    {
        if (!$user->customer) {
            throw new Exception(__('no data found'));
        }

        $loyaltyDiscount = LoyaltyDiscount::where('id', $id)
            ->first();
        if (!$loyaltyDiscount) {
            throw new Exception(__('no data found'));
        }
        return new LoyaltyDiscountResource($loyaltyDiscount);
    }


    public function redeemedLoyaltyDiscounts(User $user)
    {
        if ($user->customer) {
            $redeemedDiscounts = LoyaltyDiscountCustomer::where('customer_id', $user->customer->id)
                ->with('loyaltyDiscount', 'discountType')
                ->orderByDesc('created_at')
                ->paginate(10);

            return LoyaltyDiscountCustomerResource::collection($redeemedDiscounts);
        } else {
            throw new Exception(__('user should be customer'));
        }

    }

    public function availableForUseRedeemedLoyaltyDiscounts(User $user)
    {
        if ($user->customer) {
            $redeemedDiscounts = LoyaltyDiscountCustomer::where('customer_id', $user->customer->id)
                ->where('is_used',0)
                ->with('loyaltyDiscount', 'discountType')
                ->orderByDesc('created_at')
                ->get();

            return LoyaltyDiscountCustomerResource::collection($redeemedDiscounts);
        } else {
            throw new Exception(__('user should be customer'));
        }

    }

    public function loyaltyDiscounts()
    {
        $discounts = LoyaltyDiscount::with('loyaltyDiscountCustomers', 'discountType')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->get();
        return LoyaltyDiscountResource::collection($discounts);

    }

    public function redeem(User $user, $id)
    {
        try {
            //check auth user is customer
            if (!$user->customer) {
                throw new Exception(__('user should be customer'));
            }
            $customer = $user->customer;
            $loyaltyDiscount = (new CheckLoyaltyDiscountMutation())->handle($id, $customer);
            //success check
            DB::beginTransaction();
            $this->createLoyaltyDiscountCustomer($loyaltyDiscount, $customer);
            $this->decrementCustomerPoints($customer, $loyaltyDiscount->points);
            DB::commit();
            return $loyaltyDiscount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

    }

    private function createLoyaltyDiscountCustomer($loyaltyDiscount, $customer)
    {
        return LoyaltyDiscountCustomer::create([
            'loyalty_discount_id' => $loyaltyDiscount->id,
            'points' => $loyaltyDiscount->points,
            'discount_type_id' => $loyaltyDiscount->discount_type_id,
            'discount_amount' => $loyaltyDiscount->discount_amount,
            'maximum_discount' => $loyaltyDiscount->maximum_discount,
            'minimum_amount' => $loyaltyDiscount->minimum_amount,
            'customer_id' => $customer->id,
        ]);
    }

    private function decrementCustomerPoints($customer, $points)
    {
        //add total to provider wallet
        (new CreatePointTransactionMutation())
            ->handle(
                $customer,
                $points,
                TransactionType::OUT,
                "you redeem loyalty points discount you spent [ $points ] points",
                false,
                "لقد استبدلت خصم نقاط الولاء، وقد أنفقت [ $points ] نقطة"
            );
    }

    public function verifyLoyaltyDiscount(User $user, int $id): array
    {
        try {
            //check auth user is customer
            if (!$user->customer) {
                throw new Exception(__('user should be customer'));
            }
            $customer = $user->customer;

            $loyalty_discount = LoyaltyDiscountCustomer::where('id', $id)->first();
            if (!$loyalty_discount) {
                throw new \Exception(__('loyalty discount not found'));
            }

            //check usage
            (new CheckLoyaltyDiscountUsageMutation())->handle($id, $customer);

            //check cart
            $customer_cart = auth()->user()->customer ?->cart;
            if ($customer_cart->cartItems->isEmpty()) {
                throw new \Exception(__('Cart is empty'));
            }

            $customer_total_cart = $customer_cart->total;
            $customer_total_amount_due = $customer_cart->amount_due;
            $minimum_amount=$loyalty_discount->minimum_amount;
            //check minimum amount
            if ($customer_total_cart < $minimum_amount) {
                throw new \Exception(__("this discount not applicable , minimum amount to use this discount is")." [ $minimum_amount ]");
            }

            //calculate promo code
            $discount = (new LoyaltyDiscountCalculationsMutation())->handle($loyalty_discount, $customer_total_cart);
            $discount_amount = $discount . ' SAR ';

            $total_after_discount = $customer_total_cart - $discount;
            $amount_due_after_discount =max(0, $customer_total_amount_due - $discount);
            return [
                'id' => $loyalty_discount->id,
                'discount_type' => $loyalty_discount->discountType->title,
                'discount_amount' => $discount_amount,
                'total_after_discount' => $total_after_discount,
                'amount_due_after_discount' => $amount_due_after_discount,
            ];
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }


    }
}
