<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashoutRequest extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'status',
        'bank_name',
        'account_number',
        'account_name',
        'iban',
    ];

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
