<?php

namespace App\Filament\Resources\CustomerCampaignResource\Pages;

use App\Filament\Resources\CustomerCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerCampaign extends CreateRecord
{
    protected static string $resource = CustomerCampaignResource::class;

    protected function getActions(): array
    {
        return [

        ];
    }
}
