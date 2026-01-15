<?php

namespace App\Filament\Resources\LoyaltyDiscountResource\Pages;

use App\Filament\Resources\LoyaltyDiscountResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RedeemedCustomers extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LoyaltyDiscountResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Redeemed Customers');
    }

    protected static string $view = 'filament.resources.loyalty-discount-resource.pages.redeemed-customers';


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }

}
