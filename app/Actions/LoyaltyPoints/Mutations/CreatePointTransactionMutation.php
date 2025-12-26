<?php

namespace App\Actions\LoyaltyPoints\Mutations;

use App\Http\Resources\PointTransactionResource;
use App\Models\Customer;
use App\Models\Enums\TransactionType;
use App\Models\PointTransaction;
use App\Notifications\LoyaltyPointTransactionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreatePointTransactionMutation
{
    public function handle(Customer $customer, int $points, TransactionType $type, string $description, $sendNotification = false, $description_ar = null): PointTransactionResource
    {
        if (($type === TransactionType::OUT) && $customer->points < $points) {
            throw ValidationException::withMessages([
                'points' => 'You don\'t have enough points to redeem',
            ]);
        }

        $transaction = PointTransaction::create([
            'customer_id' => $customer->id,
            'points' => $points,
            'type' => $type,
            'description' => $description,
        ]);

        if ($sendNotification == true) {
            //notification
            try {
                $body = $description;
                $body_ar = $description_ar;
                $customer->user->notify(new LoyaltyPointTransactionNotification($body, $body_ar));
            } catch (\Exception $e) {
                Log::info($e);
            }
        }

        return new PointTransactionResource($transaction);
    }
}
