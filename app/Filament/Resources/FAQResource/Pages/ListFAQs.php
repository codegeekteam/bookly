<?php

namespace App\Filament\Resources\FAQResource\Pages;

use App\Filament\Resources\FAQResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFAQs extends ListRecords
{
    protected static string $resource = FAQResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
