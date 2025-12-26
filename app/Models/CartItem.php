<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'attached_service_id',
        'quantity',
        'picked_date',
        'time_slot',
        'delivery_type_id',
        'address_id',
        'employee_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function attachedService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttachedService::class);
    }

    public function deliveryType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->price,
        );
    }
    public function total(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->price * $this->quantity,
        );
    }

    public function amountDue(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->calculateAmountDue(),
        );
    }

    private function calculateAmountDue(): float
    {
        if (!$this->attachedService) {
            return 0;
        }

        $price = $this->attachedService->price;
        $beneficiaries = $this->quantity;

        if ($this->attachedService->has_deposit) {
            $depositPrice = ($this->attachedService->deposit / 100) * $price;
            return $depositPrice * $beneficiaries;
        }

        return $price * $beneficiaries;
    }

    public function hasDeposit(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->has_deposit,
        );
    }

    public function deposit(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->deposit ?? 0,
        );
    }

    public function depositAmount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->deposit_amount,
        );
    }

    public function serviceId(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->service_id,
        );
    }
    public function isFavorite(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->favourites()
                                   ->where('customer_id', auth('sanctum')->user()?->customer?->id)
                                    ->exists(),
        );
    }

    public function rating(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->service->reviews()->avg('rate'),
        );
    }

    public function providerId():Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->serviceProvider->id,
        );
    }
    public function providerName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->serviceProvider->name,
        );
    }

    public function providerImage(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->serviceProvider?->getMedia('images')->last()?->getUrl()
                               ?? asset('assets/default.jpg')
        );
    }

    public function providerType(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->serviceProvider?->providerType?->title,
        );
    }

    public function title(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->attachedService->service?->getTranslation('title', request()->header('lang') ?? 'en'),
        );
    }

    public function deliveryTypeTitle(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->deliveryType->title,
        );
    }
}
