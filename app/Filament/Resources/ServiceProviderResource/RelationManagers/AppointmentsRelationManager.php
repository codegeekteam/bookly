<?php

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use App\Models\Appointment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public static function getLabel(): ?string
    {
        return __('Appointment');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Appointments');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Appointments');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([

                    DatePicker::make('date')
                        ->native(false)
                        ->minDate(now()->format('Y-m-d'))
                        ->displayFormat('d-m-Y')
                        ->label(__('Date'))
                        ->required(),

                    TimePicker::make('time_from')
                        ->label(__('Time From'))
                        ->seconds(false)
                        ->after('now')
                        ->required(),

                    TimePicker::make('time_to')
                        ->label(__('Time From'))
                        ->seconds(false)
                        ->after('time_from')
                        ->required(),

                    Select::make('service_id')
                        ->relationship('service', 'title')
                        ->options(\App\Models\Service::whereHas('attachedServices', function ($query) {
                            $query->where('service_provider_id', $this->ownerRecord->id);
                        })->get()->pluck('title', 'id'))
                        ->searchable()
                        ->placeholder('Select Service')
                        ->preload(),

                    Select::make('customer_id')
                        ->relationship('customer', 'first_name')
                        ->options(\App\Models\Customer::get()->mapWithKeys(function ($customer) {
                            return [$customer->id => $customer->first_name.' '.$customer->last_name];
                        })->toArray())
                        ->live()
                        ->searchable()
                        ->preload(),

                    Select::make('status_id')
                        ->relationship('status', 'title')
                        ->options(\App\Models\AppointmentStatus::all()->pluck('title', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Select Status')
                        ->preload(),
                    TextInput::make('buffer_time_in_minutes')
                        ->label(__('Buffer Time'))
                        ->numeric()
                        ->suffix(__('Minutes'))
                        ->default(30),

                ])->columns(2),

                Section::make()->schema([

                    Placeholder::make('created_at')
                        ->label('Created Date')
                        ->content(fn (?Appointment $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label('Last Modified Date')
                        ->content(fn (?Appointment $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date(),

                TextColumn::make('time_from')
                    ->label(__('Time From'))
                    ->time('g:i A'),

                TextColumn::make('time_to')
                    ->label(__('Time To'))
                    ->time('g:i A'),

                TextColumn::make('service.title')
                    ->label(__('Service')),

                TextColumn::make('customer.first_name')

                    ->label(__('Customer')),

                TextColumn::make('status.title')
                    ->badge()
                    ->label(__('Status')),
                TextColumn::make('buffer_time_in_minutes')
                    ->label(__('Buffer Time'))
                    ->searchable()
                    ->badge()
                    ->suffix(__(' Minutes'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function ($data) {

                    $buffer_time = $data['buffer_time_in_minutes'];
                    $duration = $data['service']->duration_in_minutes;
                    $data['time_to'] = $data['time_from']->addMinutes($buffer_time + $duration);

                    return $data;
                }),
            ]);
    }
}
