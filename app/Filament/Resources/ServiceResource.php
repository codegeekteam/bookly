<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers\ReviewsRelationManager;
use App\Models\Service;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string
    {
        return __('Services');
    }

    public static function getLabel(): ?string
    {
        return __('Service');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Services');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Service Details'))->schema([
                    TextInput::make('arabic_title')->label(__('Arabic Title'))->required(),
                    TextInput::make('english_title')->label(__('English Title'))->required(),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->required(),

                    Select::make('categories')
                        ->label(__('Categories'))
                        ->relationship('categories', 'title')
                        ->options(\App\Models\Category::get()->pluck('title', 'id')->toArray())
                        ->multiple()
                        ->required()
                        ->preload(),

                    /*SpatieMediaLibraryFileUpload::make('image')
                        ->collection('images')
                        ->rules('mimes:png,jpg,jpeg')
                        ->label(__('Image')),*/

                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->required(),

                ])->columns(2),
                Section::make()->schema([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?Service $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?Service $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
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

                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(query: function ($query, $search) {
                        $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%']);
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->sortable()
                    ->wrap(50),
                ToggleColumn::make('is_active')

                    ->label(__('Active'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.title')
                    ->label(__('Categories'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->separator(','),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        1 => __('Active'),
                        0 => __('Not Active'),
                    ])
                    ->label(__('Active')),
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'title')
                    ->label(__('Category'))
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                    ->multiple()
                    ->options(\App\Models\Category::get()->pluck('title', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
