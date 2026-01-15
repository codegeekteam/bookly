<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getActions(): array
    {
        return [

        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $plan = \App\Models\Plan::find($data['plan_id']);
        $data['amount_paid'] = $plan->price;
        // Also, automatically calculate expires_at based on selected plan
        $numberOfMonths = $plan->number_of_months ?? 0;
        $expiresAt = now()->addMonths($numberOfMonths)->format('Y-m-d');
        $data['start_date']=now();
        $data['expires_at']= $expiresAt;
        $data['payment_status']='paid';
        return Subscription::create($data);
    }
}
