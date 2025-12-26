<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OperationalOffHour */
class OperationalOffHoursResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'day' => $this->getDayOfTheWeekShortName($this->day_of_week),
            'start_time' => Carbon::parse($this->start_time)->format('H:i'),
            'end_time' => Carbon::parse($this->end_time)->format('H:i'),
        ];
    }

    public function getDayOfTheWeekShortName(string $day)
    {
        return match($day){
        'Saturday' => 'SAT',
            'Sunday' => 'SUN',
            'Monday' => 'MON',
            'Tuesday' => 'TUE',
            'Wednesday' => 'WED',
            'Thursday' => 'THU',
            'Friday' => 'FRI',
            default => $day,
        };
    }
}
