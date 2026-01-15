<?php

namespace App\Filament\Resources\LoyaltyDiscountResource\Pages;

use App\Filament\Resources\LoyaltyDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyDiscount extends EditRecord
{
    protected static string $resource = LoyaltyDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
