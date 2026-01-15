<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CustomerLineChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Trend Chart';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $data = Trend::model(Customer::class);

        if ($activeFilter == 'today' || empty($activeFilter)) {
            $data = $data->between(start: now()->startOfDay(), end: now()->endOfDay())->perHour()->count();
        } elseif ($activeFilter == 'week') {
            $data = $data->between(start: now()->startOfWeek(), end: now()->endOfWeek())->perDay()->count();
        } elseif ($activeFilter == 'month') {
            $data = $data->between(start: now()->startOfMonth(), end: now()->endOfMonth())->perDay()->count();
        } elseif ($activeFilter == 'year') {
            $data = $data->between(start: now()->startOfYear(), end: now()->endOfYear())->perMonth()->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Customer',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'year' => 'This year',
        ];
    }
}
