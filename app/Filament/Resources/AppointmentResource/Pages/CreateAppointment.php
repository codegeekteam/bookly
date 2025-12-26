<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        foreach ($this->data['AppointmentServices'] as &$AppointmentService) {
            $AppointmentService['end_time'] = $AppointmentService['start_time'];
        }

        return $data;

    }
}
