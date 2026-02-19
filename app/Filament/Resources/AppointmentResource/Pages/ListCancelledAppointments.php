<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\List;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCancelledAppointments extends ListRecords
{
    // protected static string $resource = AppointmentResource::class;

    // protected static ?string $navigationGroup = 'Appointments';
    // protected static ?string $navigationLabel = 'Cancelled Appointments';
    // protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    // protected static bool $shouldRegisterNavigation = true;


    // protected function getTableQuery(): ?Builder
    // {
    //     // Adjust column name if your status column is different
    //     return parent::getTableQuery()->where('status_id', AppointmentStatus::Cancelled->value);
    // }

    // protected function getHeaderActions(): array
    // {
    //     return []; // disables "Create" button
    // }

}
