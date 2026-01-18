<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiftCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'user_id',
        'amount',
        'recipient_name',
        'recipient_email',
        'recipient_phone_number',
        'is_used',
        'used_by',
        'appointment_id',
        'payment_status',
        'gift_card_theme_id',
        'used_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');

    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'used_by');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(GiftCardTheme::class, 'gift_card_theme_id');
    }

    public function refundLogs()
    {
        return $this->morphMany(RefundLog::class, 'model');
    }
}
