<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'total_amount',
        'due_date',
        'status',
        'receipt_path',
        'cancellation_note',
        'transferred_at',
        'payment_transferred_date',
        'transaction_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'transferred_at' => 'datetime',
    ];

    /**
     * Get the service provider that owns the payout
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the deferred payouts that belong to this payout
     */
    public function deferredPayouts(): HasMany
    {
        return $this->hasMany(DeferredPayout::class);
    }

    /**
     * Scope a query to only include pending payouts
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include transferred payouts
     */
    public function scopeTransferred(Builder $query): Builder
    {
        return $query->where('status', 'transferred');
    }

    /**
     * Scope a query to only include payouts for a specific provider
     */
    public function scopeForProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('service_provider_id', $providerId);
    }

    /**
     * Scope a query to filter payouts by due date range
     */
    public function scopeDueDateRange(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('due_date', [$from, $to]);
    }
}
