<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'response_code',
        'status',
        'merchant_reference',
        'amount',
        'currency',
        'appointment_id',
        'fort_id',
        'response'
    ];
}
