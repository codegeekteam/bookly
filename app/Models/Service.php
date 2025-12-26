<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia,
        SoftDeletes;

    public $translatable = ['title'];

    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_service');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ServiceProvider::class, 'attached_services')->withPivot('price');
    }

    public function attachedServices()
    {
        return $this->hasMany(AttachedService::class, 'service_id');
    }

    public function operationalHours(): HasMany
    {
        return $this->hasMany(OperationalHour::class, 'service_id');
    }

    public function operationalOffHours(): HasMany
    {
        return $this->hasMany(OperationalOffHour::class, 'service_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_service')->withPivot('date', 'start_time', 'end_time', 'number_of_beneficiaries');
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function images()
    {
        return Attribute::make(
            get: fn ($value) => $this->getFirstMediaUrl('images') ? $this->getMedia('images')->last()->getUrl() : asset('assets/default.jpg'),
            set: fn ($value) => $this->addMedia($value)->toMediaCollection('images')
        );

    }

    public function customerCampaign()
    {
        return $this->belongsToMany(CustomerCampaign::class, 'campaign_service');
    }

    public function promoCode()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_service');
    }



    public function heldTimeSlots()
    {
        return $this->hasMany(HeldTimeSlot::class);
    }

}
