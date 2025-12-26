<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutSettingResource\Pages;
use App\Filament\Resources\PayoutSettingResource\RelationManagers;
use App\Models\PayoutSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayoutSettingResource extends Resource
{
    protected static ?string $model = PayoutSetting::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = 'Payout Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payout Configuration')
                    ->description('Configure the holding period and payout schedule for service providers')
                    ->schema([
                        Forms\Components\TextInput::make('holding_period_days')
                            ->label('Holding Period (Days)')
                            ->helperText('Number of days to hold payment after appointment completion')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(7),

                        Forms\Components\CheckboxList::make('payout_days')
                            ->label('Payout Days')
                            ->helperText('Select the days of the week when payouts are processed')
                            ->options([
                                0 => 'Sunday',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                            ])
                            ->required()
                            ->columns(4)
                            ->gridDirection('row'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('holding_period_days')
                    ->label('Holding Period')
                    ->suffix(' days')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payout_days')
                    ->label('Payout Days')
                    ->state(function ($record) {
                        $payoutDays = $record->payout_days;
                        if (empty($payoutDays)) {
                            return ['Not configured'];
                        }
                        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        return collect($payoutDays)
                            ->map(fn($day) => $days[$day] ?? '')
                            ->filter()
                            ->values()
                            ->all();
                    })
                    ->badge()
                    ->searchable(false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for singleton
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayoutSettings::route('/'),
        ];
    }    
}
