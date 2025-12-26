<?php

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use App\Models\OperationalHour;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OperationalHoursRelationManager extends RelationManager
{
    protected static string $relationship = 'operationalHours';

    public array $days_of_week = [
        'Sunday' => 'Sunday',
        'Monday' => 'Monday',
        'Tuesday' => 'Tuesday',
        'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday',
        'Friday' => 'Friday',
        'Saturday' => 'Saturday',
    ];

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Operational Hours');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Checkbox::make('is_for_all_services')
                    ->label(__('For All Services'))
                    ->default(false)
                    ->hiddenOn('edit')
                    ->live(),
                Checkbox::make('is_for_all_days')
                    ->label(__('For All Days'))
                    ->default(false)
                    ->hiddenOn('edit')
                    ->live(),
                Select::make('service_id')
                    ->disabled(fn ($get) => $get('is_for_all_services'))
                    ->options(function () {
                        return \App\Models\Service::whereHas('attachedServices', function ($query) {
                            $query->where('service_provider_id', $this->ownerRecord->id);
                        })->get()->pluck('title', 'id')->toArray();
                    })
                    ->label(__('Service'))
                    ->multiple()
                    ->required(fn ($get) => ! $get('is_for_all_services'))
                    ->placeholder(__('Select Service'))
                    ->hiddenOn('edit'),
                Select::make('day_of_week')
                    ->options(collect($this->days_of_week))
                    ->live()
                    ->multiple()
                    ->required(fn ($get) => ! $get('is_for_all_days'))
                    ->disabled(fn ($get) => $get('is_for_all_days'))
                    ->placeholder(__('Select Day of Week'))
                    ->hiddenOn('edit'),
                Select::make('service_id')
                    ->relationship('service', 'title')
                    ->options(\App\Models\Service::whereHas('attachedServices', function ($query) {
                        $query->where('service_provider_id', $this->ownerRecord->id);
                    })->get()->pluck('title', 'id'))
                    ->placeholder(__('Select Service'))
                    ->hiddenOn('create'),
                Select::make('day_of_week')
                    ->options(collect($this->days_of_week)->filter(function ($value, $key) {
                        return $key !== 'All';
                    }))
                    ->live()
                    ->placeholder(__('Select Day of Week'))
                    ->hiddenOn('create'),
                TimePicker::make('start_time')
                    ->displayFormat('h:i A')
                    ->seconds(false)
                    ->label(__('Start Time'))
                    ->required(),
                TimePicker::make('end_time')
                    ->displayFormat('h:i A')
                    ->seconds(false)
                    ->after('start_time')
                    ->label(__('End Time'))
                    ->required(),
                TextInput::make('duration_in_minutes')
                    ->label(__('Duration'))
                    ->numeric()
                    ->suffix(__('Minutes'))
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.title')
                    ->label(__('Service'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->label(__('Days of Week'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->badge()
                    ->label(__('Start Time'))
                    ->searchable()
                    ->sortable()
                    ->time('h:i A'),
                TextColumn::make('end_time')
                    ->badge()
                    ->label(__('End Time'))
                    ->searchable()
                    ->sortable()
                    ->time('h:i A'),

                TextColumn::make('duration_in_minutes')
                    ->label(__('Duration'))
                    ->searchable()
                    ->badge()
                    ->suffix(' '.__('Minutes'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label(__('Service'))
                    ->relationship('service', 'title',
                        fn ($query) => $query->whereHas('attachedServices', function ($query) {
                            $query->where('service_provider_id', $this->ownerRecord->id);
                        }))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                    ->native(false)
                    ->placeholder(__('Select Service')),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->before(function (CreateAction $action, $record, $data) {
                    if ($data['is_for_all_services'] && $data['is_for_all_days']) {
                        OperationalHour::where('service_provider_id', $this->ownerRecord->id)->delete();

                        $services = \App\Models\Service::whereHas('attachedServices', function ($query) {
                            $query->where('service_provider_id', $this->ownerRecord->id);
                        })->get();

                        foreach ($services as $service) {

                            foreach (collect($this->days_of_week)->filter(fn ($k, $v) => $k !== 'All') as $day) {
                                OperationalHour::create([
                                    'service_provider_id' => $this->ownerRecord->id,
                                    'service_id' => $service->id,
                                    'day_of_week' => $day,
                                    'start_time' => $data['start_time'],
                                    'end_time' => $data['end_time'],
                                    'duration_in_minutes' => $data['duration_in_minutes'],
                                ]);
                            }
                        }
                    } elseif ($data['is_for_all_services'] && ! $data['is_for_all_days']) {

                        OperationalHour::where('service_provider_id', $this->ownerRecord->id)
                            ->whereIn('day_of_week', $data['day_of_week'])
                            ->delete();

                        $services = \App\Models\Service::whereHas('attachedServices', function ($query) {
                            $query->where('service_provider_id', $this->ownerRecord->id);
                        })->get();

                        foreach ($data['day_of_week'] as $day) {
                            foreach ($services as $service) {
                                OperationalHour::create([
                                    'service_provider_id' => $this->ownerRecord->id,
                                    'service_id' => $service->id,
                                    'day_of_week' => $day,
                                    'start_time' => $data['start_time'],
                                    'end_time' => $data['end_time'],
                                    'duration_in_minutes' => $data['duration_in_minutes'],
                                ]);
                            }
                        }
                    } elseif (! $data['is_for_all_services'] && $data['is_for_all_days']) {
                        OperationalHour::where('service_provider_id', $this->ownerRecord->id)
                            ->whereIn('service_id', $data['service_id'])
                            ->delete();
                        foreach (collect($this->days_of_week)->filter(fn ($k, $v) => $k !== 'All') as $day) {
                            foreach ($data['service_id'] as $service_id) {
                                OperationalHour::create([
                                    'service_provider_id' => $this->ownerRecord->id,
                                    'service_id' => $service_id,
                                    'day_of_week' => $day,
                                    'start_time' => $data['start_time'],
                                    'end_time' => $data['end_time'],
                                    'duration_in_minutes' => $data['duration_in_minutes'],
                                ]);
                            }
                        }
                    } else {
                        OperationalHour::where('service_provider_id', $this->ownerRecord->id)
                            ->whereIn('service_id', $data['service_id'])
                            ->whereIn('day_of_week', $data['day_of_week'])
                            ->delete();

                        foreach ($data['day_of_week'] as $day) {

                            foreach ($data['service_id'] as $service_id) {

                                OperationalHour::create([
                                    'service_provider_id' => $this->ownerRecord->id,
                                    'service_id' => $service_id,
                                    'day_of_week' => $day,
                                    'start_time' => $data['start_time'],
                                    'end_time' => $data['end_time'],
                                    'duration_in_minutes' => $data['duration_in_minutes'],
                                ]);
                            }
                        }
                    }
                    $action->cancel();
                }),
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
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPluralLabel(): ?string
    {
        return __('Operational Hours');
    }

    public static function getLabel(): ?string
    {
        return __('Operational Hour');
    }
}
