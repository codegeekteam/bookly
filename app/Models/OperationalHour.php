<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationalHour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'service_provider_id',
        'day_of_week',
        'start_time',
        'end_time',
        'duration_in_minutes',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function ServiceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
