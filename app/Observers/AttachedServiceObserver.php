<?php

namespace App\Observers;

use App\Models\AttachedService;
use App\Models\OperationalHour;

class AttachedServiceObserver
{
    /**
     * Handle the AttachedService "created" event.
     */
    public function created(AttachedService $attachedService): void
    {
        OperationalHour::create([
            'attached_service_id' => $attachedService->id,
            'service_provider_id' => $attachedService->service_provider_id,
            'saturday' => ['start_time' => null, 'end_time' => null],
            'sunday' => ['start_time' => null, 'end_time' => null],
            'monday' => ['start_time' => null, 'end_time' => null],
            'tuesday' => ['start_time' => null, 'end_time' => null],
            'wednesday' => ['start_time' => null, 'end_time' => null],
            'thursday' => ['start_time' => null, 'end_time' => null],
            'friday' => ['start_time' => null, 'end_time' => null],
        ]);
    }

    /**
     * Handle the AttachedService "updated" event.
     */
    public function updated(AttachedService $attachedService): void
    {
        //
    }

    /**
     * Handle the AttachedService "deleted" event.
     */
    public function deleted(AttachedService $attachedService): void
    {
        //
    }

    /**
     * Handle the AttachedService "restored" event.
     */
    public function restored(AttachedService $attachedService): void
    {
        //
    }

    /**
     * Handle the AttachedService "force deleted" event.
     */
    public function forceDeleted(AttachedService $attachedService): void
    {
        //
    }
}
