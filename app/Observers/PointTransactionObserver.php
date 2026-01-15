<?php

namespace App\Observers;

use App\Models\Enums\TransactionType;
use App\Models\PointTransaction;

class PointTransactionObserver
{
    /**
     * Handle the pointTransaction "created" event.
     */
    public function created(PointTransaction $pointTransaction): void
    {
        $customer = $pointTransaction->customer;
        if ($pointTransaction->type === TransactionType::IN) {
            $customer->points += $pointTransaction->points;

        } else {
            $customer->points -= $pointTransaction->points;
        }
        $customer->save();
    }

    /**
     * Handle the pointTransaction "updated" event.
     */
    public function updated(pointTransaction $pointTransaction): void
    {
        //
    }

    /**
     * Handle the pointTransaction "deleted" event.
     */
    public function deleted(pointTransaction $pointTransaction): void
    {
        //
    }

    /**
     * Handle the pointTransaction "restored" event.
     */
    public function restored(pointTransaction $pointTransaction): void
    {
        //
    }

    /**
     * Handle the pointTransaction "force deleted" event.
     */
    public function forceDeleted(pointTransaction $pointTransaction): void
    {
        //
    }
}
