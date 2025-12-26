<?php

namespace App\Filament\Resources\GiftCardThemeResource\Pages;

use App\Filament\Resources\GiftCardThemeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGiftCardTheme extends EditRecord
{
    protected static string $resource = GiftCardThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
