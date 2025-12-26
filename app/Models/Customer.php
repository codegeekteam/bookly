<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'is_blocked',
        'user_id',
        'date_of_birth',
        'referral_id',
        'refer_code',
        'points'
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function profilePicture()
    {
        return Attribute::make(
            get: fn ($value
            ) => $this->getFirstMediaUrl('profile_picture') ? $this->getMedia('profile_picture')->last()->getUrl() : null,
            set: fn ($value) => $this->addMedia($value)->toMediaCollection('profile_picture')
        );
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function profileCompletionPercentage()
    {
        $percent = 0;
        if ($this->first_name) {
            $percent += 25;

        }
        if ($this->last_name) {
            $percent += 25;
        }
        if ($this->phone_number) {
            $percent += 25;
        }
        if ($this->email) {
            $percent += 25;
        }

        return $percent;

    }

    public function getRemainingFields(): array
    {
        $fields = [];
        if (! $this->first_name) {
            $fields[] = 'first_name';
        }
        if (! $this->last_name) {
            $fields[] = 'last_name';
        }
        if (! $this->phone_number) {
            $fields[] = 'phone_number';
        }
        if (! $this->email) {
            $fields[] = 'email';
        }

        return $fields;
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function fullName(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referral_id');
    }

    public function referrals()
    {
        return $this->hasMany(Customer::class, 'referral_id');
    }

    public function usedGiftCards()
    {
        return $this->hasMany(GiftCard::class, 'used_by');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'customer_id');
    }

}
