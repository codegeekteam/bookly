<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'data' => PaymentMethod::where('is_active', true)
                ->whereIn('name', ['Card', 'Cash'])
                ->get()
                ->map(function (PaymentMethod $paymentMethod) {
                    return [
                        'id' => $paymentMethod->id,
                        'title' => $paymentMethod->name,
                        'icon' => $paymentMethod->icon,
                    ];
                }),
        ]);
    }
}
