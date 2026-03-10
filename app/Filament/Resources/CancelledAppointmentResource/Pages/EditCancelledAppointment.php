<?php

namespace App\Filament\Resources\CancelledAppointmentResource\Pages;

use App\Filament\Resources\CancelledAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCancelledAppointment extends EditRecord
{
    protected static string $resource = CancelledAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
