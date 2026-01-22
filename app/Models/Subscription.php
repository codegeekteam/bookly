<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'user_id',
        'amount_paid',
        'expires_at',
        'start_date',
        'payment_status'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'start_date' => 'datetime',
        'plan_id' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function refundLogs()
    {
        return $this->morphMany(RefundLog::class, 'model');
    }


}
