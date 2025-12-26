<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProviderResource\Pages;
use App\Filament\Resources\ServiceProviderResource\RelationManagers\BankDetailsRelationManager;
use App\Filament\Resources\ServiceProviderResource\RelationManagers\EmployeesRelationManager;
use App\Filament\Resources\ServiceProviderResource\RelationManagers\OperationalHoursRelationManager;
use App\Filament\Resources\ServiceProviderResource\RelationManagers\ServicesRelationManager;
use App\Models\ServiceProvider;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ServiceProviderResource extends Resource
{
    protected static ?string $model = ServiceProvider::class;

    protected static ?string $slug = 'service-providers';

    protected static ?string $recordTitleAttribute = 'title';

    public string $address_name;

    public float $latitude;

    public float $longitude;

    public string $address_details;

    public static function getNavigationGroup(): string
    {
        return __('Users');
    }

    public static function getLabel(): ?string
    {
        return __('Service Provider');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Service Providers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Service Provider Details'))
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('phone_number')
                            ->label(__('Phone Number'))
                            ->required()
                            ->rules('required|numeric|digits:9')
                            ->unique(ignoreRecord: true),
                        Textarea::make('biography')
                            ->label(__('Biography')),
                        Select::make('provider_type_id')
                            ->relationship('providerType', 'title')
                            ->required()
                            ->label(__('Provider Type'))
                            ->placeholder(__('Select Provider Type'))
                            ->preload()
                            ->searchable(),
                        Toggle::make('is_blocked')
                            ->label(__('Blocked')),
                        Toggle::make('published')
                            ->label(__('Published'))
                            ->default(true),
                        /*Toggle::make('is_active')
                            ->label(__('Active')),*/
                    ])->columns(2),

                Section::make(__('Legal Details'))
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextInput::make('commercial_register')
                            ->label(__('Commercial Register').' (معروف)'),
                    ])
                    ->columns(2),

                Section::make(__('Social Media'))
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        TextInput::make('twitter')
                            ->label(__('Twitter')),
                        TextInput::make('snapchat')
                            ->label(__('Snapchat')),
                        TextInput::make('instagram')
                            ->label(__('Instagram')),
                        TextInput::make('tiktok')
                            ->label(__('Tiktok')),
                    ])
                    ->columns(2),

                Section::make(__('Address Details'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Repeater::make('addresses')
                            ->label(__('Addresses'))
                            ->relationship('addresses')
                            ->schema([
                                TextInput::make('address_name')
                                    ->required()
                                    ->label(__('Address')),
                                TextArea::make('address_details')
                                    ->required()
                                    ->label(__('Address Details')),

                                TextInput::make('latitude')
                                    ->label(__('Latitude'))
                                    ->numeric()
                                    ->required(),
                                TextInput::make('longitude')
                                    ->required()
                                    ->numeric()
                                    ->label(__('Longitude')),

                            ])->columns(2),
                    ]),

                Section::make(__('Cancellation Policy'))
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        Toggle::make('cancellation_enabled')
                            ->label(__('Enable Cancellation Policy'))
                            ->helperText(__('Allow customers to cancel appointments based on the policy below'))
                            ->reactive(),
                        TextInput::make('cancellation_hours_before')
                            ->label(__('Cancellation Hours Before Appointment'))
                            ->helperText(__('Number of hours before the appointment when customers can cancel. For example, 24 means customers can cancel up to 24 hours before the appointment.'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(168)
                            ->suffix('hours')
                            ->visible(fn ($get) => $get('cancellation_enabled') === true),
                    ])->columns(2),

                Section::make(__('Booking Lead Time Settings'))
                    ->icon('heroicon-o-clock')
                    ->description(__('Configure how far in advance customers can book appointments'))
                    ->schema([
                        TextInput::make('minimum_booking_lead_time_hours')
                            ->label(__('Minimum Booking Lead Time'))
                            ->helperText(__('Minimum hours in advance customers must book (0-6 months = 0-4320 hours). For example, 24 means customers must book at least 24 hours (1 day) before. Leave empty for no minimum.'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(4320)
                            ->suffix('hours')
                            ->placeholder('0'),
                        TextInput::make('maximum_booking_lead_time_months')
                            ->label(__('Maximum Booking Lead Time'))
                            ->helperText(__('Maximum months in advance customers can book. For example, 3 means customers can only book up to 3 months ahead. Leave empty for no maximum.'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->suffix('months')
                            ->placeholder('12'),
                    ])->columns(2),

                Section::make(__('Media'))
                    ->icon('heroicon-o-photo')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('Service Provider Image'))
                            ->collection('service_provider_images')
                            ->multiple()
                            ->image(),
                        SpatieMediaLibraryFileUpload::make('service_provider_profile_image')
                            ->label(__('Profile Picture'))
                            ->collection('service_provider_profile_image')
                            ->image(),
                    ])->columns(1),

                Section::make('')
                    ->hidden(fn ($context) => $context == 'create')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label(__('Created Date'))
                            ->content(fn (?ServiceProvider $record
                            ): string => $record?->created_at?->diffForHumans() ?? '-'),

                        Placeholder::make('updated_at')
                            ->label(__('Last Modified Date'))
                            ->content(fn (?ServiceProvider $record
                            ): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])->columns(2),

            ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->default(fn ($record) => $record->name ?? $record->phone_number)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label(__('Phone Number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('biography')
                    ->label(__('Biography'))
                    ->searchable()
                    ->wrap()
                    ->sortable(),

                TextColumn::make('services.title')
                    ->label(__('Services'))
                    ->badge()
                    ->separator(','),

                TextColumn::make('providerType.title')
                    ->label(__('Provider Type'))
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.activeSubscription.plan.number_of_months')
                    ->label(__('Subscribed Plans'))
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('minimum_booking_lead_time_hours')
                    ->label(__('Min Lead Time'))
                    ->suffix(' hrs')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('maximum_booking_lead_time_months')
                    ->label(__('Max Lead Time'))
                    ->suffix(' months')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('published')
                    ->label(__('Published'))
                    ->sortable(),

            ])->filters([
                SelectFilter::make('services')
                    ->relationship('services', 'title')
                    ->multiple()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                    ->options(\App\Models\Service::get()->pluck('title', 'id')->toArray())
                    ->placeholder(__('Select Services')),

                SelectFilter::make('provider_type_id')
                    ->relationship('providerType', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                    ->label(__('Provider Type'))
                    ->preload()
                    ->multiple(),

            ])->bulkActions([
                ExportBulkAction::make(),
                //DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceProviders::route('/'),
            'create' => Pages\CreateServiceProvider::route('/create'),
            'edit' => Pages\EditServiceProvider::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getRelations(): array
    {
        return [
            ServicesRelationManager::class,
            EmployeesRelationManager::class,
            OperationalHoursRelationManager::class,
            BankDetailsRelationManager::class,
            //AppointmentsRelationManager::class,
        ];
    }
}
