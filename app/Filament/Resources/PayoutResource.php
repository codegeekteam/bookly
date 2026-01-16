<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Payout;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ServiceProvider;
use App\Services\PayoutService;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PayoutResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PayoutResource\RelationManagers;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = 'Payouts';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payout Details')
                    ->schema([
                        Forms\Components\Select::make('service_provider_id')
                            ->label('Service Provider')
                            ->relationship('serviceProvider', 'name', fn ($query) => $query->whereNotNull('name'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($record) => $record !== null)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('SAR')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'transferred' => 'Transferred',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->disabled()
                            ->columnSpan(1),

                        // Forms\Components\FileUpload::make('receipt_path')
                        //     ->label('Transfer Receipt')
                        //     ->directory('payout-receipts')
                        //     ->disk('public')
                        //     ->image()
                        //     ->maxSize(5120)
                        //     ->visible(fn ($record) => $record && $record->status === 'transferred')
                        //     ->columnSpanFull(),

                        Forms\Components\View::make('filament.receipt_view')
                        ->visible(fn ($record) => $record && $record->receipt_path)
                        ->label('Transfer Receipt')
                        ->columnSpanFull(),


                        Forms\Components\Textarea::make('cancellation_note')
                            ->label('Cancellation Note')
                            ->rows(3)
                            ->visible(fn ($record) => $record && $record->status === 'cancelled')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('transferred_at')
                            ->label('Transferred At')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->status === 'transferred')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('serviceProvider.name')
                    ->label('Service Provider')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'transferred',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('transferred_at')
                    ->label('Transferred At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date', 'desc')
            ->filters([
                SelectFilter::make('service_provider_id')
                    ->label('Service Provider')
                    ->relationship('serviceProvider', 'name', fn ($query) => $query->whereNotNull('name'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'transferred' => 'Transferred',
                        'cancelled' => 'Cancelled',
                    ]),

                Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from')
                            ->label('Due From'),
                        Forms\Components\DatePicker::make('due_until')
                            ->label('Due Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\Action::make('markAsTransferred')
                    ->label('Mark as Transferred')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('transfer_date')
                            ->label('Transfer Date')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('receipt')
                            ->label('Transfer Receipt (Optional)')
                            ->directory('payout-receipts')
                            ->disk('public')
                            ->image()
                            ->maxSize(5120),
                    ])
                    ->action(function (Payout $record, array $data) {
                        $payoutService = app(PayoutService::class);
                        $receipt = $data['receipt'] ?? null;
                        $transferDate = $data['transfer_date'];
                        $transactionId = $data['transaction_id'];

                        $payoutService->markAsTransferred($record, $transferDate, $transactionId, $receipt);

                        Notification::make()
                            ->title('Payout Transferred')
                            ->success()
                            ->body('The payout has been marked as transferred and email sent to provider.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Payout $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Payout $record, array $data) {
                        $payoutService = app(PayoutService::class);
                        $payoutService->cancelPayout($record, $data['note']);

                        Notification::make()
                            ->title('Payout Cancelled')
                            ->warning()
                            ->body('The payout has been cancelled and amounts returned to pending.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Payout $record) => $record->status === 'pending'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for now
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\PayoutExport($records),
                            'payouts.xlsx'
                        );
                    })
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
            'index' => Pages\ListPayouts::route('/'),
            'view' => Pages\ViewPayout::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
