<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function total(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->cartItems->sum(function (CartItem $cartItem) {
                return $cartItem->attachedService->price * $cartItem->quantity;
            }),
        );
    }

    public function amountDue(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->cartItems->sum(function (CartItem $cartItem) {
                $price = $cartItem->attachedService->price;
                $beneficiaries = $cartItem->quantity; // your number_of_beneficiaries equivalent

                if ($cartItem->attachedService->has_deposit) {
                    $depositPrice = ($cartItem->attachedService->deposit / 100) * $price;
                    return $depositPrice * $beneficiaries;
                }

                return $price * $beneficiaries;
            }),
        );
    }

    public function getFirstPickedDateAttribute()
    {
        return $this->cartItems()->select('picked_date')->first()?->picked_date;
    }
}
