<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DeferredPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'service_provider_id',
        'amount',
        'payment_type',
        'payment_method_id',
        'completed_at',
        'available_at',
        'status',
        'payout_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'available_at' => 'datetime',
    ];

    /**
     * Get the appointment that owns the deferred payout
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the service provider that owns the deferred payout
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the payment method used
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the payout this deferred payout belongs to
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    /**
     * Scope a query to only include pending deferred payouts
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include deferred payouts available for payout
     */
    public function scopeAvailableForPayout(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('available_at', '<=', now());
    }

    /**
     * Scope a query to only include deferred payouts for a specific provider
     */
    public function scopeForProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('service_provider_id', $providerId);
    }

    /**
     * Scope a query to only include deferred payouts that are not yet available
     */
    public function scopeNotYetAvailable(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('available_at', '>', now());
    }
}
