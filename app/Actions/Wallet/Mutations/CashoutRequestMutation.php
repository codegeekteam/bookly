<?php

namespace App\Actions\Wallet\Mutations;

use App\Http\Requests\WalletCashoutRequest;
use App\Models\CashoutRequest;
use App\Models\Enums\TransactionType;
use App\Models\Wallet;
use Illuminate\Validation\ValidationException;

class CashoutRequestMutation
{
    public function handle(WalletCashoutRequest $request)
    {
        if (! auth()->user()->serviceProvider) {
            throw ValidationException::withMessages([
                'amount' => 'Only service providers can cashout',
            ]);
        }

        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();
        $amount = $request->get('full_amount') ? $wallet->balance : $request->get('amount');

        if ($wallet->balance < 1) {
            throw ValidationException::withMessages([
                'amount' => 'You don\'t have any balance to payout',
            ]);
        }

        if ($wallet->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'You don\'t have enough balance to payout',
            ]);
        }

        $cashout = CashoutRequest::create([
            'user_id' => auth()->id(),
            'bank_name' => $request->get('bank_name'),
            'account_number' => $request->get('account_number'),
            'account_name' => $request->get('account_name'),
            'iban' => $request->get('iban'),
            'amount' => $amount,
            'wallet_id' => $wallet->id,
        ]);

        (new CreateWalletTransactionMutation())
            ->handle($wallet, $amount, TransactionType::OUT, 'Payout request');

        return $cashout;

    }
}
