<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('Total number of customers'), Customer::all()->count()),
            Stat::make(__('Average customers on wait'), 4.9),
            Stat::make(__('Number of customers booked a service for this month'), 5),
        ];
    }
}
