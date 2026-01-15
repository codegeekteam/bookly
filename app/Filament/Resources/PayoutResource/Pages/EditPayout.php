<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EditPayout extends EditRecord
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Delete is disabled - payouts can only be cancelled, not deleted
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            PayoutResource\Widgets\DeferredPayoutsTable::class,
        ];
    }
}
