<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_refund',
        'wallet_refund',
    ];
}
