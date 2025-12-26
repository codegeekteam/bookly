<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttachedService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'service_provider_id',
        'price',
        'description',
        'has_deposit',
        'deposit'
    ];

    protected $casts = [
        'price' => 'float',
        'has_deposit' => 'boolean',
        'deposit' => 'float'
    ];

    public function deliveryTypes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryType::class, 'attached_delivery_types');
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->whereNull('deleted_at');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'attached_service_id');
    }

    /**
     * @return Collection
     */
    public function operationalHours()
    {
        return OperationalHour::query()
            ->where('service_id', $this->service_id)
            ->where('service_provider_id', $this->service_provider_id)->get();
    }

    public function operationalOffHours()
    {
        return OperationalOffHour::query()
            ->where('service_id', $this->service_id)
            ->where('service_provider_id', $this->service_provider_id)->get();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'attached_service_id');
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class, 'service_id');
    }

    public function getDepositAmountAttribute()
    {
        if(!$this->has_deposit)
        {
            return 0;
        }
        $deposit=($this->price * $this->deposit)/100;
        return (float)round($deposit,2);

    }
}
