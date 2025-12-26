<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldTimeSlot extends Model
{
    protected $casts = [
        'date' => 'date',
        'expires_at' => 'datetime',
        'timeSlot' => 'datetime',
    ];

    protected $fillable = [
        'service_id',
        'date',
        'service_provider_id',
        'expires_at',
        'timeSlot',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }


    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }


    public function getIsExpiredAttribute(): bool
    {
        return now()->greaterThan($this->expires_at);
    }




}
