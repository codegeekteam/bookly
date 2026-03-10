<?php

namespace App\Filament\Resources\CancelledAppointmentResource\Pages;

use App\Filament\Resources\CancelledAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCancelledAppointments extends ListRecords
{
    protected static string $resource = CancelledAppointmentResource::class;

    protected static ?string $title = 'Cancelled Appointments';

     // Optionally override breadcrumb label
    public function getBreadcrumb(): string
    {
        return 'Cancelled List';
    }

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
        ];
    }
}
