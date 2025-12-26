<?php

namespace App\Rules;

use App\Models\OperationalHour;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class DateWithinOperationalHoursRule implements ValidationRule
{
    public function __construct(
        protected string $service_id,
        protected string $service_provider_id
    ) {

    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $day_of_week = Carbon::parse($value)->format('l');
        $operational_hours = OperationalHour::query()
            ->where('day_of_week', $day_of_week)
            ->where('service_id', $this->service_id)
            ->where('service_provider_id', $this->service_provider_id)->exists();

        if (! $operational_hours) {
            $fail('The selected :attribute date is not within the operational hours.');
        }
    }
}
