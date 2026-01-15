<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
class ReferralDetails extends Page
{
    use InteractsWithRecord;
    protected static string $resource = CustomerResource::class;
    public  function getTitle(): string | Htmlable
    {
        return __('View Referrals');
    }
    protected static string $view = 'filament.resources.customer-resource.pages.referral-details';


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'referCode' => $this->record->refer_code,
            'referrer' => $this->record->referrer,
            'referralCount' => $this->record->referrals()->count(),
            'record' => $this->record,
        ];
    }

}
