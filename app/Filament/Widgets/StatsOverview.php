<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $customerStats = self::getModelMonthlyComparison(new Customer);
        $serviceStats = self::getModelMonthlyComparison(new Service);
        $appointmentStats = self::getModelMonthlyComparison(new Appointment);

        return [
            Stat::make(__('Total Customers'), $customerStats['count'])
                ->description($customerStats['percentage'].'% '.ucfirst($customerStats['indication']).' Compared to Last Month')
                ->descriptionIcon($customerStats['icon'])
                ->color($customerStats['color']),

            Stat::make(__('Total Services'), $serviceStats['count'])
                ->description($serviceStats['percentage'].'% '.ucfirst($customerStats['indication']).' Compared to Last Month')
                ->descriptionIcon($serviceStats['icon'])
                ->color($serviceStats['color']),

            Stat::make(__('Total Appointments'), $appointmentStats['count'])
                ->description($appointmentStats['percentage'].'% '.ucfirst($customerStats['indication']).' Compared to Last Month')
                ->descriptionIcon($appointmentStats['icon'])
                ->color($appointmentStats['color']),
        ];
    }

    private static function getModelMonthlyComparison(Model $model): array
    {
        $model_count_overall = $model::all()->count('id');
        $model_count_this_month = $model::whereMonth('created_at', Carbon::now()->month())->count('id');
        $model_count_previous_month = $model::whereDate('created_at', Carbon::now()->month()->subMonth())->count('id');
        $change = $model_count_this_month - $model_count_previous_month;

        if ($model_count_previous_month !== 0) {
            $percentage = ($change / $model_count_previous_month) * 100;
        } else {
            $percentage = 0;
        }
        if ($change > 0) {
            $indication = 'increase';
            $icon = 'heroicon-o-arrow-trending-up';
            $color = 'success';
        } elseif ($change < 0) {
            $indication = 'decrease';
            $icon = 'heroicon-o-arrow-trending-down';
            $color = 'danger';
        } else {
            $indication = 'draw';
            $icon = 'heroicon-o-ellipsis-horizontal';
            $color = 'warning';
        }

        return [
            'count' => $model_count_overall,
            'difference' => $change,
            'percentage' => $percentage,
            'indication' => $indication,
            'icon' => $icon,
            'color' => $color,
        ];
    }
}
