<?php

namespace App\Models;

use App\Enums\AppointmentStatus as AppointmentState;
use App\StateMachines\Appointment\BaseAppointmentState;
use App\StateMachines\Appointment\CanceledState;
use App\StateMachines\Appointment\CompletedState;
use App\StateMachines\Appointment\ConfirmedState;
use App\StateMachines\Appointment\PendingState;
use App\StateMachines\Appointment\RejectedState;
use App\StateMachines\Appointment\RescheduleRequestState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var \Illuminate\Support\HigherOrderCollectionProxy|mixed
     */
    protected $fillable = [
        'service_provider_id',
        'customer_id',
        'promo_code_id',
        'comment',
        'status_id',
        'payment_method_id',
        'gift_card_id',
        'changed_status_at',
        'payment_status',
        'previous_status_id',
        'total',
        'total_payed',
        'wallet_amount',
        'card_amount',
        'discount',
        'loyalty_discount_customer_id',
        'amount_due',
        'deposit_amount',
        'deposit_payment_status',
        'deposit_payment_method_id',
        'remaining_amount',
        'remaining_payment_status',
        'remaining_payment_method_id'
    ];

    protected $casts = [
        'date' => 'datetime',
        'time_from' => 'datetime',
        'time_to' => 'datetime',
        'on_hold' => 'boolean',
        'status_id' => 'integer',
        'changed_status_at' => 'datetime',
        'amount_due' => 'float',
        'deposit_amount' => 'float',
        'remaining_amount' => 'float',
        'total' => 'float',
        'total_payed' => 'float',
        'wallet_amount' => 'float',
        'card_amount' => 'float',
        'discount' => 'float'
    ];

    public function state(): BaseAppointmentState
    {
        return match ($this->status_id) {
            AppointmentState::Cancelled->value => new CanceledState($this),
            AppointmentState::Confirmed->value => new ConfirmedState($this),
            AppointmentState::RescheduleRequest->value => new RescheduleRequestState($this),
            AppointmentState::Rejected->value => new RejectedState($this),
            AppointmentState::Completed->value => new CompletedState($this),
            default => new PendingState($this),
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id')->withTrashed();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AppointmentStatus::class);
    }

    public function previousStatus(): BelongsTo
    {
        return $this->belongsTo(AppointmentStatus::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function bookingType(): BelongsTo
    {
        return $this->belongsTo(BookingType::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_service')->withPivot('date', 'start_time', 'end_time', 'number_of_beneficiaries', 'delivery_type_id', 'address_id', 'new_end_time', 'new_start_time', 'new_date' , 'accepted_reschedule', 'employee_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function depositPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'deposit_payment_method_id');
    }

    public function remainingPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'remaining_payment_method_id');
    }

    public function giftCard()
    {
        return $this->HasOne(GiftCard::class);
    }

    public function loyaltyDiscountCustomer()
    {
        return $this->belongsTo(LoyaltyDiscountCustomer::class,'loyalty_discount_customer_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(ChatConversation::class);
    }
}
