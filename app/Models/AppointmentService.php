<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AppointmentService extends Pivot
{
    //this model is created in order to use Repeater in filament/AppointmentResource as per filament documentation

    protected $fillable = ['date', 'start_time', 'end_time', 'number_of_beneficiaries', 'delivery_type_id', 'address_id', 'service_id','new_end_time','new_start_time', 'new_date' ,'accepted_reschedule', 'employee_id'];

    public $timestamps = false;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
