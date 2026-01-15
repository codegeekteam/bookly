<?php

namespace App\Filament\Resources\GiftCardThemeResource\Pages;

use App\Filament\Resources\GiftCardThemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGiftCardThemes extends ListRecords
{
    protected static string $resource = GiftCardThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
