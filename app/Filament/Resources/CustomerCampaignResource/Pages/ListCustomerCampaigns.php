<?php

namespace App\Filament\Resources\CustomerCampaignResource\Pages;

use App\Filament\Resources\CustomerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerCampaigns extends ListRecords
{
    protected static string $resource = CustomerCampaignResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
