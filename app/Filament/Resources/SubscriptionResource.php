<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $slug = 'subscriptions';

    //protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): string
    {
        return __('Subscriptions');
    }

    public static function getLabel(): ?string
    {
        return __('Subscription');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Subscriptions');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('')->schema([
                Select::make('plan_id')
                    ->label(__('Plan'))
                    ->relationship('plan', 'number_of_months')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record?->number_of_months)
                    ->required()
                    ->reactive() // Trigger updates when the plan changes
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Automatically calculate expires_at based on selected plan
                        if ($state) {
                            $plan = \App\Models\Plan::find($state);
                            $set('amount_paid', $plan ? $plan->price : 0);

                            // Also, automatically calculate expires_at based on selected plan
                            $numberOfMonths = $plan->number_of_months ?? 0;
                            $expiresAt = now()->addMonths($numberOfMonths)->format('Y-m-d');
                            $set('expires_at', $expiresAt);
                            $set('payment_status', 'paid');
                        }
                    }),

                Select::make('user_id')
                    ->label(__('User'))
                    ->options(function () {
                        return \App\Models\User::query()
                            ->whereDoesntHave('activeSubscription')
                            ->whereHas('serviceProvider')
                            ->with('serviceProvider')
                            ->get()
                            ->map(function ($user) {
                                return [
                                    'key' => $user->id,
                                    'value' => self::getUserPhoneOrName($user),
                                ];
                            })
                            ->pluck('value', 'key')
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),

                TextInput::make('amount_paid')
                    ->label(__('Amount Paid'))
                    ->disabled()
                    ->reactive(),

                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->default(now())
                    ->reactive()// Default value is the current date
                    ->disabled(),    // Make the field read-only

                DatePicker::make('expires_at')
                    ->label(__('Expires Date'))
                    ->disabled()
                    ->reactive()// Make the field read-only
                    ->default(now()),
            ])->columns(2),

        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('plan.number_of_months')
                    ->searchable()
                    ->sortable()
                    ->label(__('number of months')),

                TextColumn::make('user')
                    ->label(__('User'))
                    ->formatStateUsing(fn($record) => self::getUserPhoneOrName($record->user))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label(__('Amount Paid'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->searchable()
                    ->sortable()
                    ->money()
                    ->label(__('Expires Date'))
                    ->date(),

                TextColumn::make('start_date')
                    ->searchable()
                    ->sortable()
                    ->money()
                    ->label(__('Start Date'))
                    ->date(),

                TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable()
                    ->label(__('Payment Status'))
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'paid' => __('Paid'),
                            'unpaid' => __('Unpaid'),
                            'partially_paid' => __('Partially Paid'),
                            default => __('Unknown'),
                        };
                    }),
            ])->filters([
                //
                SelectFilter::make('payment_status')
                    ->options([
                        'paid' => __('Paid'),
                        'unpaid' => __('Unpaid'),
                    ])
                    ->label(__('Payment Status'))
                    ->attribute('payment_status'),
                Filter::make('start_date')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until')->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    })
            ])->actions([
                Tables\Actions\Action::make('Edit Payment Status')
                    ->label(__('Edit Payment Status'))
                    ->form([
                        \Filament\Forms\Components\Select::make('payment_status')
                            ->label(__('Payment Status'))
                            ->options([
                                'paid' => __('Paid'),
                                'unpaid' => __('Unpaid'),
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['payment_status' => $data['payment_status']]);
                    }),
                //Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            //'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    private static function getUserPhoneOrName($record): ?string
    {
        $account = '';
        if ($record->customer) {
            $account = $record->customer;
            if ($account->first_name == '' || $account->last_name == '') {
                return $account->phone_number;
            }

            return $account->first_name . ' ' . $account->last_name;
        } elseif ($record->serviceProvider) {
            $account = $record->serviceProvider;
            if ($account->name == '') {
                return $account->phone_number;
            }

            return $account->name;
        }

        return $account;
    }
}
