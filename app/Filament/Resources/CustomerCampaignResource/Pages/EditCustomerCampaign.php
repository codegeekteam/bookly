<?php

namespace App\Filament\Resources\CustomerCampaignResource\Pages;

use App\Filament\Resources\CustomerCampaignResource;
use App\Models\CustomerCampaign;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerCampaign extends EditRecord
{
    protected static string $resource = CustomerCampaignResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['is_active']) {
            CustomerCampaign::all()->except($this->record->id)->each(function ($campaign) {
                $campaign->update(['is_active' => false]);
            });
        }

        return $data;
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
