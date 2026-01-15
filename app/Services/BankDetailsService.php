<?php

namespace App\Services;

use App\Models\BankDetails;
use Exception;

class BankDetailsService
{
    public function index($request)
    {
        $details = BankDetails::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
        return $details;
    }

    public function create($request)
    {
        $user = auth()->user();
        if (!$user->serviceProvider) {
            throw new Exception(__('no data found'));
        }
        $details = BankDetails::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'bank_name' => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => $request->account_number,
                'iban' => $request->iban,
                'swift_code' => $request->swift_code,
            ]);
        return $details;

    }

    public function update($request)
    {
        $user = auth()->user();
        if (!$user->serviceProvider) {
            throw new Exception(__('no data found'));
        }

        $details = BankDetails::where('user_id', auth()->id())->where('id', $request->id)->first();
        if (!$details) {
            throw new Exception(__('no data found'));
        }
        $details->update([
            'bank_name' => $request->bank_name ?? $details->bank_name,
            'account_holder_name' => $request->account_holder_name ?? $details->account_holder_name,
            'account_number' => $request->account_number ?? $details->account_number,
            'iban' => $request->iban ?? $details->iban,
            'swift_code' => $request->swift_code ?? $details->swift_code,
        ]);

        return $details;
    }

    public function delete($details_id)
    {
        $user = auth()->user();
        if (!$user->serviceProvider) {
            throw new Exception(__('no data found'));
        }
        $details = BankDetails::where('user_id', auth()->id())->where('id', $details_id)->first();
        if (!$details) {
            throw new Exception(__('no data found'));
        }
        $details->delete();
        return true;

    }

}
