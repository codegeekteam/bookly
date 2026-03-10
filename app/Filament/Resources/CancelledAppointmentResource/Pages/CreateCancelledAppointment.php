<?php

namespace App\Filament\Resources\CancelledAppointmentResource\Pages;

use App\Filament\Resources\CancelledAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCancelledAppointment extends CreateRecord
{
    protected static string $resource = CancelledAppointmentResource::class;
}
