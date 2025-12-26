<?php

namespace App\Filament\Resources\PayoutResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\DeferredPayout;
use Illuminate\Database\Eloquent\Builder;

class DeferredPayoutsTable extends BaseWidget
{
    public ?\App\Models\Payout $record = null;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Appointment Breakdown')
            ->description('Detailed list of all appointments included in this payout')
            ->query(
                DeferredPayout::query()
                    ->where('payout_id', $this->record?->id)
                    ->with(['appointment', 'paymentMethod'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('appointment.id')
                    ->label('Appointment ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('appointment.appointmentServices')
                    ->label('Service Date')
                    ->formatStateUsing(function ($record) {
                        $firstService = $record->appointment->appointmentServices->first();
                        if (!$firstService) {
                            return 'N/A';
                        }
                        $date = $firstService->date;
                        if ($date instanceof \Carbon\Carbon) {
                            return $date->format('Y-m-d');
                        }
                        return is_string($date) ? $date : 'N/A';
                    }),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Payment Type')
                    ->badge()
                    ->colors([
                        'info' => 'deposit',
                        'success' => 'remaining',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Payment Method')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_at')
                    ->label('Available At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('appointment.id', 'asc');
    }
}
