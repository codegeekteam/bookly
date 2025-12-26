<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $slug = 'categories';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getLabel(): ?string
    {
        return __('Category');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Categories');
    }

    public static function getNavigationGroup(): string
    {
        return __('Main Settings');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(__('Category Details'))->schema([
                    TextInput::make('arabic_title')->label(__('Arabic Title'))->required(),
                    TextInput::make('english_title')->label(__('English Title'))->required(),
                    Toggle::make('is_active')->label(__('Active'))->default(true),
                ])->columns(2),

                Section::make(__('Category Icon'))->schema([
                    SpatieMediaLibraryFileUpload::make('icon')
                        ->collection('category_images')
                        ->rules('mimes:png,jpg,jpeg')
                        ->label(__('Icon')),
                ])->columns(1),

                Section::make()->schema([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?Category $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?Category $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->sortable()
                    ->searchable(query: function ($query, $search) {
                        $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%']);
                    }),
                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->sortable()
                    ->searchable(),
            ])->BulkActions([
                ExportBulkAction::make(),
                DeleteBulkAction::make(),
            ])->filters([
                SelectFilter::make('is_active')
                    ->options([
                        1 => __('Active'),
                        0 => __('Not Active'),
                    ])
                    ->label(__('Active')),
            ]);
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
