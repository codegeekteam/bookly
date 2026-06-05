<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Customer */
class CustomerResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
$completion = $this->profileCompletionPercentage();
$profile_picture = $this->getFirstMediaUrl('profile_picture') ?? null;
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'date_of_birth' => $this->date_of_birth,
            'is_blocked' => $this->is_blocked,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
          //  'profile_picture' => $this->getFirstMediaUrl('profile_picture') ? $this->getMedia('profile_picture')->last()->getUrl() : asset('assets/default.jpg'),
          'profile_picture' => $profile_picture ? $profile_picture : asset('assets/default.jpg'),
          'profile_complete_percentage' => $completion, //$this->profileCompletionPercentage(),
            'remaining_profile_fields' => $this->getRemainingFields(),
            'refer_code' => $this->refer_code,
            'points' => $this->points,
            // 'write_code' => ($this->profileCompletionPercentage() != 100 && !$this->referral_id) ? true : false,
            'write_code' => ($completion != 100 && !$this->referral_id) ? true : false,
        ];
    }
}
