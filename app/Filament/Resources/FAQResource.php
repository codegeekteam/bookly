<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FAQResource\Pages;
use App\Models\FAQ;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FAQResource extends Resource
{
    protected static ?string $model = FAQ::class;

    protected static ?string $slug = 'f-a-qs';

    //protected static ?string $recordTitleAttribute = 'id';

    public static function getLabel(): ?string
    {
        return __('Frequently Asked Question');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Frequently Asked Questions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Main Settings');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make(__('Question'))->schema([
                Textarea::make('arabic_question')
                    ->label(__('Question In Arabic'))->required(),
                Textarea::make('english_question')
                    ->label(__('Question In English'))->required(),
            ])->columns(2),

            Section::make(__('Answer'))->schema([
                Textarea::make('arabic_answer')
                    ->label(__('Answer In Arabic'))->required(),
                Textarea::make('english_answer')
                    ->label(__('Answer In English'))->required(),
            ])->columns(2),

            Section::make()->schema([
                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn (?FAQ $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn (?FAQ $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ])->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('question')
                ->searchable(query: function ($query, $search) {
                    $query->whereRaw('LOWER(question) LIKE ?', ['%' . strtolower($search) . '%']);
                })
                ->sortable()
                ->wrap()
                ->limit(10)
                ->label(__('Question')),
            TextColumn::make('answer')
                ->searchable(query: function ($query, $search) {
                    $query->whereRaw('LOWER(answer) LIKE ?', ['%' . strtolower($search) . '%']);
                })
                ->sortable()
                ->wrap()
                ->limit(10)
                ->label(__('Answer')),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFAQs::route('/'),
            'create' => Pages\CreateFAQ::route('/create'),
            'edit' => Pages\EditFAQ::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['question','answer'];
    }
}
