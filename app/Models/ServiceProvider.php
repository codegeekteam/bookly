<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ServiceProvider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'is_blocked',
        'published',
        'email',
        'biography',
        'phone_number',
        'user_id',
        'provider_type_id',
        'is_active',
        'commercial_register',
        'social',
        'max_appointments_per_day',
        'deposit_type',
        'deposit_amount',
        'cancellation_enabled',
        'cancellation_hours_before',
        'minimum_booking_lead_time_hours',
        'maximum_booking_lead_time_months',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'published' => 'boolean',
        'social' => 'json',
        'cancellation_enabled' => 'boolean',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'attached_services')->whereNull('services.deleted_at')->withPivot('price');
    }

    public function attachedServices()
    {
        return $this->hasMany(AttachedService::class, 'service_provider_id')
            ->whereNull('deleted_at')
            ->whereHas('service', function ($query) {
                $query->whereNull('deleted_at');
            });
    }

    public function images()
    {
        return Attribute::make(
            get: fn ($value) => $this->getFirstMediaUrl('images') ? $this->getMedia('images')->last()->getUrl() : null,
            set: fn ($value) => $this->addMedia($value)->toMediaCollection('images')
        );

    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'provider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function operationalHours()
    {
        return $this->hasMany(OperationalHour::class, 'service_provider_id');
    }

    public function operationalOffHours()
    {
        return $this->hasMany(OperationalOffHour::class, 'service_provider_id');
    }

    public function getMinStartTime()
    {
        $minStartTime = $this->operationalHours()->min('start_time');
        return $minStartTime??'00:00:00';
    }

    public function getMaxEndTime()
    {
        $maxEndTime = $this->operationalHours()->max('end_time');
        return $maxEndTime??'23:00:00';
    }

    public function getMinServicePrice()
    {
        $min = $this->attachedServices()->min('price');
        return $min ? $min.'' : '0';
    }

    public function getMaxServicePrice()
    {
        $max = $this->attachedServices()->max('price');
        return $max ? $max.'' : '0';
    }

    public function profileCompletionPercentage(): int
    {
        $percentage = 0;
        if ($this->name) {
            $percentage += 20;
        }
        if ($this->biography) {
            $percentage += 10;
        }
        if ($this->phone_number) {
            $percentage += 20;
        }
        //        if ($this->commercial_register) {
        //            $percentage += 10;
        //        }
        if ($this->addresses()->count() > 0) {
            $percentage += 10;
        }
        if ($this->providerType) {
            $percentage += 10;
        }
        if ($this->max_appointments_per_day) {
            $percentage += 10;
        }
        if ($this->deposit_type) {
            $percentage += 10;
        }
        if ($this->deposit_amount) {
            $percentage += 10;
        }

        return $percentage;

    }

    public function getRemainingFields(): array
    {
        $fields = [];

        if (! $this->name || ! $this->biography || ! $this->phone_number || ! $this->providerType) {

            $fields[] = 'personal_info';

        }
        if ($this->addresses()->count() == 0) {
            $fields[] = 'address';
        }

        if (! $this->max_appointments_per_day || ! $this->deposit_type || ! $this->deposit_amount) {
            $fields[] = 'booking_settings';
        }

        return $fields;
    }

    public function customerCampaign()
    {
        return $this->belongsToMany(CustomerCampaign::class, 'campaign_service');
    }

    public function getAvgResponseTimeAttribute()
    {
        return Cache::remember('avg_response_time_'.$this->id, 60 * 60, function () {
            $appointments = $this->appointments()
                ->whereNotNull('changed_status_at')
                ->get();

            $count = $appointments->count();

            $total = 0;
            foreach ($appointments as $appointment) {
                $total += $appointment->created_at->diffInMinutes($appointment->changed_status_at);
            }

            if ($count === 0) {
                return 'Usually responds within minutes';
            }
            if ($total > 60) {
                return 'Usually responds within '.round($total / $count / 60).' hours';
            }

            return 'Usually responds within '.round($total / $count).' minutes';
        });
    }

    public function heldTimeSlots()
    {
        return $this->hasMany(HeldTimeSlot::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(BankDetails::class, 'user_id', 'user_id');
    }
}
