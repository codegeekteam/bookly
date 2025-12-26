<?php

namespace App\Actions\Wallet\Queries;

use App\Http\Resources\PayoutResource;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;

class GetWalletQuery
{
    public function handle(): WalletResource|PayoutResource
    {
        $wallet = Wallet::where('user_id', auth()->id())->with('transactions')->first();
        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => auth()->id(),
                'balance' => 0,
            ]);
        }

        if (auth()->user()->serviceProvider) {
            return new PayoutResource($wallet);
        }

        return new WalletResource($wallet);
    }
}
