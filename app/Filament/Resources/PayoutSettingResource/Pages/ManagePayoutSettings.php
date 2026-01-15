<?php

namespace App\Filament\Resources\PayoutSettingResource\Pages;

use App\Filament\Resources\PayoutSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePayoutSettings extends ManageRecords
{
    protected static string $resource = PayoutSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - this is a singleton settings page
            // The record is automatically created via migration
        ];
    }
}
