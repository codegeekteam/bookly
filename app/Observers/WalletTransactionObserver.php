<?php

namespace App\Observers;

use App\Models\Enums\TransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class WalletTransactionObserver
{
    /**
     * Handle the WalletTransaction "created" event.
     */
    public function created(WalletTransaction $walletTransaction): void
    {
        $wallet = Wallet::find($walletTransaction->wallet_id);
        if ($walletTransaction->type === TransactionType::IN) {
            if ($wallet->user->serviceProvider) {
                $wallet->pending_balance += $walletTransaction->amount;
            } else {
                $wallet->balance += $walletTransaction->amount;
            }
        } else {
            $wallet->balance -= $walletTransaction->amount;
        }
        $wallet->save();
    }

    /**
     * Handle the WalletTransaction "updated" event.
     */
    public function updated(WalletTransaction $walletTransaction): void
    {
        //
    }

    /**
     * Handle the WalletTransaction "deleted" event.
     */
    public function deleted(WalletTransaction $walletTransaction): void
    {
        //
    }

    /**
     * Handle the WalletTransaction "restored" event.
     */
    public function restored(WalletTransaction $walletTransaction): void
    {
        //
    }

    /**
     * Handle the WalletTransaction "force deleted" event.
     */
    public function forceDeleted(WalletTransaction $walletTransaction): void
    {
        //
    }
}
