<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Closure;
use Filament\Pages\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CustomerResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerStatsOverview::class,
        ];
    }

    //    protected function getTableRecordUrlUsing(): ?Closure
    //    {
    //        return null;
    //    }
}
