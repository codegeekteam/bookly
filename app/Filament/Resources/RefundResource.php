<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Refund;
use Filament\Forms\Form;
use App\Models\RefundLog;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RefundResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RefundResource\RelationManagers;


class RefundResource extends Resource
{
     protected static ?string $model = RefundLog::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Refund';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Refund List';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('info')
                    ->label('Info')
                    ->formatStateUsing(function ($record) {
                        if ($record->model instanceof \App\Models\GiftCard) {
                            return 'GiftCard: ' . $record->model->code;
                        }

                        if ($record->model instanceof \App\Models\Appointment) {
                            return 'Appointment: ' . $record->model->id;
                        }

                        if ($record->model instanceof \App\Models\Subscription) {
                            return 'Subscription: ' . $record->model->id;
                        }

                        return 'Unknown';
                    }),

                TextColumn::make('amount')
                    ->money('SAR')              
                    ->searchable()
                    ->label('Amount'),
                    // ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('d M Y h:i A') // format optional
                    ->sortable()
                    ->searchable(),
                TextColumn::make('merchant_reference')
                    ->label('Reference ID')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => '18',   // success codes
                        'danger' => '17',    // failed codes
                        'warning' => '0',
                    ])
                    ->label('Status'), 
                  
                
            ]);          
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
            'create' => Pages\CreateRefund::route('/create'),
            'edit' => Pages\EditRefund::route('/{record}/edit'),
        ];
    }    
}
