<?php

namespace App\Filament\Resources\LoyaltyDiscountResource\Pages;

use App\Filament\Resources\LoyaltyDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyDiscounts extends ListRecords
{
    protected static string $resource = LoyaltyDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
