<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use App\Filament\Resources\RegionResource\RelationManagers\RegionRelationManager;
use App\Models\Region;
use Exception;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $slug = 'regions';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string
    {
        return __('Main Settings');
    }

    public static function getLabel(): ?string
    {
        return __('Region');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Regions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(__('Region Details'))->schema([
                    TextInput::make('arabic_title')->label(__('Arabic Title'))->required(),
                    TextInput::make('english_title')->label(__('English Title'))->required(),
                ])->columns(2),

                Section::make(__('Main Region'))->schema([
                    Select::make('region_id')
                        ->label(__('Region'))
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                        ->placeholder(__('Select Region'))
                        ->relationship('region', 'title')
                        ->nullable(),
                ]),

                Section::make([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?Region $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?Region $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->forceSearchCaseInsensitive()
                    ->sortable(),

                TextColumn::make('region.title')->label(__('Main Region')),
            ])->filters([
                SelectFilter::make('region_id')
                    ->label(__('Region'))
                    ->options(
                        Region::query()
                            ->orderBy('title')
                            ->get()
                            ->mapWithKeys(fn (Region $region) => [$region->id => $region->title])
                            ->toArray()
                    ),
            ])->bulkActions([
                ExportBulkAction::make(),
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegions::route('/'),
            'create' => Pages\CreateRegion::route('/create'),
            'edit' => Pages\EditRegion::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RegionRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }
}
