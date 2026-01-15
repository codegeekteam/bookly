<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerCampaignResource\Pages;
use App\Models\CustomerCampaign;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerCampaignResource extends Resource
{
    protected static ?string $model = CustomerCampaign::class;

    protected static ?string $slug = 'customer-campaigns';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Customer App');
    }

    public static function getLabel(): ?string
    {
        return __('Campaign');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Toggle::make('is_active')
                ->label(__('Active'))
                ->required(),
            Section::make('Campaign Information')->schema([

                TextInput::make('title')
                    ->Label(__('Title'))
                    ->autofocus()
                    ->required(),

            ]),

            Section::make(__('Hot Services'))->schema([
                CheckboxList::make('services')
                    ->label(__('Services'))
                    ->relationship('services', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title ?? '-')
                    ->searchable()
                    ->columns(3)
                    ->required(),
            ]),

            Section::make(__('Popular Providers'))->schema([
                CheckboxList::make('providers')
                    ->label(__('Providers'))
                    ->relationship('providers', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? $record->phone_number)
                    ->searchable()
                    ->columns(3)
                    ->required(),
            ]),
            Section::make(__('Banner'))->schema([
                SpatieMediaLibraryFileUpload::make('banner')
                    ->label(__('Banner'))
                    ->collection('banners')
                    ->rules('mimes:png,jpg,jpeg')
                    ->multiple()
                    ->required(),
            ]),

            Section::make()->schema([

                Placeholder::make('created_at')
                    ->label(__('Created Date'))
                    ->content(fn (?CustomerCampaign $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label(__('Last Modified Date'))
                    ->content(fn (?CustomerCampaign $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {

        return $table->columns([
            TextColumn::make('title')
                ->label(__('Title'))
                ->searchable()
                ->sortable(),

            IconColumn::make('is_active')
                ->boolean()
                ->label(__('Active'))
                ->action(function (CustomerCampaign $record) {
                    $record->update(['is_active' => true]);
                    CustomerCampaign::all()->except($record->id)->each(function ($campaign) {
                        $campaign->update(['is_active' => false]);
                    });
                })
                ->sortable(),
        ])->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerCampaigns::route('/'),
            'create' => Pages\CreateCustomerCampaign::route('/create'),
            'edit' => Pages\EditCustomerCampaign::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }
}
