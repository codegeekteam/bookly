<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class ValidSlot implements Rule //ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    // public function validate(string $attribute, mixed $value, Closure $fail): void
    // {
    //     //
    // }

    public function passes($attribute, $value)
    {
        $data = json_decode($value, true);

        if (!is_array($data)) {
            return false;
        }

        return isset($data['service_id'], $data['timeslot']);
    }

    public function message()
    {
        return 'Each slot must be valid JSON containing service_id and timeslot.';
    }
}
