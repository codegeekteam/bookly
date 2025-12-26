<?php

namespace App\Actions\Wallet\Mutations;

use App\Http\Resources\WalletTransactionResource;
use App\Models\Enums\TransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\WalletTransactionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateWalletTransactionMutation
{
    public function handle(?Wallet $wallet, float $amount, TransactionType $type, string $description,$sendNotification=false,$description_ar=null): WalletTransactionResource
    {
        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => auth('sanctum')->id(),
                'balance' => 0,
            ]);
        }
        if (($type === TransactionType::OUT) && $wallet->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'You don\'t have enough balance to cashout',
            ]);
        }

        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'description_ar' => $description_ar,
        ]);

        if($sendNotification==true)
        {
            //notification
            try {
                $body = $description;
                $body_ar = $description_ar;
                $wallet->user->notify(new WalletTransactionNotification($body, $body_ar));
            } catch (\Exception $e) {
                Log::info($e);
            }
        }

        return new WalletTransactionResource($transaction);
    }
}
