<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\CancelledAppointmentResource\Pages;
use App\Filament\Resources\CancelledAppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CancelledAppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class; 

    protected static ?string $navigationGroup = 'Appointments'; 

    protected static ?string $navigationLabel = 'Cancelled Appointments'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                 TextColumn::make('serial_no')
                    ->label('S.No.')
                    ->rowIndex(),
                    
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointmentServices.service.title')
                    ->searchable()
                    ->badge()
                    ->label('Service'),

                TextColumn::make('appointmentServices.employee.name')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->default('N/A')
                    ->label('Employee'),

                TextColumn::make('serviceProvider.name')
                    ->searchable()
                    ->label('Service Provider'),

                TextColumn::make('customer.first_name')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->customer->first_name.' '.$record->customer->last_name)
                    ->label('Customer'),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    })
                    ->label('Payment Status'),

                TextColumn::make('status.title')
                    ->badge()
                    ->label('Status'),

                TextColumn::make('total')
                    ->money('SAR')
                    ->sortable()
                    ->label('Total Amount'),

            ])
            ->filters([
                //
            ])
            ->actions([
               // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListCancelledAppointments::route('/'),
            'create' => Pages\CreateCancelledAppointment::route('/create'),
            'edit' => Pages\EditCancelledAppointment::route('/{record}/edit'),
        ];
    } 
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status_id', AppointmentStatus::Cancelled->value);
    }
}
