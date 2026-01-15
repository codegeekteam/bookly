<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'response_code',
        'response_message',
        'amount',
        'status',
        'merchant_reference',
        'response'
    ];
}
