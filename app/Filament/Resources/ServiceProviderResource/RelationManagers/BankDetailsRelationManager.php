<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class BankDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankDetails';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Bank Details');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('bank_name')
                ->label(__('Bank Name'))
                ->required()
                ->maxLength(255)
                ->translateLabel(),

            TextInput::make('account_holder_name')
                ->label(__('Account Holder Name'))
                ->required()
                ->maxLength(255)
                ->translateLabel(),

            TextInput::make('account_number')
                ->label(__('Account Number'))
                ->required()
                ->maxLength(20)
                ->translateLabel(),

            TextInput::make('iban')
                ->label(__('IBAN'))
                ->required()
                ->maxLength(34)
                ->translateLabel(),

            TextInput::make('swift_code')
                ->label(__('Swift Code'))
                ->required()
                ->maxLength(11)
                ->translateLabel(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('Bank Details'))
            ->paginated(false)  // Disable pagination for HasOne
            ->columns([
                TextColumn::make('bank_name')
                    ->label(__('Bank Name'))
                    ->searchable()
                    ->translateLabel(),

                TextColumn::make('account_number')
                    ->label(__('Account Number'))
                    ->searchable()
                    ->translateLabel(),

                TextColumn::make('account_holder_name')
                    ->label(__('Account Holder Name'))
                    ->searchable()
                    ->translateLabel(),

                TextColumn::make('iban')
                    ->label(__('IBAN'))
                    ->searchable()
                    ->translateLabel(),

                TextColumn::make('swift_code')
                    ->label(__('Swift Code'))
                    ->searchable()
                    ->translateLabel(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden(fn ($livewire) => $livewire->getRelationship()->exists()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([])  // Remove bulk actions for HasOne
            ->emptyStateHeading(__('No Data'))
            ->emptyStateDescription(__('Add bank details for this service provider.'));
    }
}
