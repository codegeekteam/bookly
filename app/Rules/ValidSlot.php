<?php

namespace App\Rules;

use Closure;

use Illuminate\Contracts\Validation\ValidationRule;



class ValidSlot implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = json_decode($value, true);

        if (!is_array($data)) {
            $fail("The $attribute must be valid JSON.");
            return;
        }

        if (!isset($data['service_id'])) {
            $fail("service_id is required in $attribute.");
        }

        if (!isset($data['timeslot'])) {
            $fail("timeslot is required in $attribute.");
        }
    } 

   
}
