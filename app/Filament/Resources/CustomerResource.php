<?php

namespace App\Filament\Resources;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

use App\Models\Enums\TransactionType;
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'customers';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getNavigationGroup(): string
    {
        return __('Users');
    }

    public static function getLabel(): ?string
    {
        return __('Customer');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Customer Details'))->schema([
                    TextInput::make('first_name')
                        ->label(__('First Name'))
                        ->maxLength(255)
                        ->validationAttribute('first name')
                        ->required(),
                    TextInput::make('last_name')
                        ->label(__('Last Name'))
                        ->maxLength(255)
                        ->validationAttribute('last name')
                        ->required(),
                    TextInput::make('email')
                        ->unique(ignoreRecord: true)
                        ->label(__('Email'))
                        ->validationAttribute('email')
                        ->email()
                        ->required(),
                    DatePicker::make('date_of_birth')
                        ->label(__('Date of Birth'))
                        ->before('today')
                        ->validationAttribute('date of birth')
                        ->required(),
                    Toggle::make('is_blocked')
                        ->validationAttribute('blocked')
                        ->label(__('Blocked')),

                    TextInput::make('phone_number')
                        ->validationAttribute('phone number')
                        ->rules('required|numeric|digits:9')
                        ->unique(ignoreRecord: true)
                        ->label(__('Phone Number'))
                        ->required(),
                ])->columns(2),

                Section::make(__('Customer Profile Picture'))->schema([
                    SpatieMediaLibraryFileUpload::make('profilePicture')
                        ->collection('profile_picture')
                        ->acceptedFileTypes(['image/jpg', 'image/jpeg', 'image/png'])
                        ->label(__('Profile Picture')),

                ]),

                Section::make()->schema([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?Customer $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?Customer $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                    Placeholder::make('points')
                     ->label(__('Points'))
                     ->content(fn (?Customer $record): string => $record?->points ?? 0),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->columns([
                ToggleColumn::make('is_blocked')
                    ->label(__('Blocked')),

                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->default(fn ($record) => $record->first_name ?? $record->phone_number)
                    ->truncate(30)
                    ->label(__('First Name')),
                TextColumn::make('last_name')
                    ->label(__('Last Name'))
                    ->searchable()
                    ->truncate(30)
                    ->default(fn ($record) => $record->first_name ?? $record->phone_number)
                    ->sortable(),
                TextColumn::make('date_of_birth')
                    ->label(__('Date of Birth'))
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
                // Add the balance column
                TextColumn::make('wallet_balance')
                    ->label(__('Wallet Balance'))
                    ->getStateUsing(fn ($record) => $record->user->wallet->balance ?? 'N/A'),
                // Add the points column
                TextColumn::make('points')
                    ->label(__('Points'))
                    ->getStateUsing(fn ($record) => $record->points ?? 0),

            ])->filters([

            ])->actions([
                EditAction::make(),
                // Add your custom Add Balance action
                Action::make('addBalance')
                    ->label (__('Add Balance'))
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->minValue(1)
                            ->numeric()
                            ->required(),
                        TextInput::make('description')
                            ->label(__('Description'))
                            ->default('Added Balance From Admin')
                            ->required(),
                        TextInput::make('description_ar')
                            ->regex('/^[\x{0600}-\x{06FF}\s]+$/u')
                            ->label(__('Arabic Description'))
                            ->default('اضافة رصيد من خلال الأدمن')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        // Handle wallet transaction creation
                        $wallet = $record->user->wallet;
                        (new CreateWalletTransactionMutation())->handle(
                            $wallet,
                            $data['amount'],
                            TransactionType::IN,
                            $data['description'],
                            true,
                            $data['description_ar']
                        );
                        // Show success notification
                        Notification::make()
                            ->title('Balance Added')
                            ->success()
                            ->body("Successfully added {$data['amount']} to customer wallet.")
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('viewReferrals')
                    ->label(__('View Referrals'))
                    ->icon('heroicon-o-user-group')
                    ->url(fn ($record) => static::getUrl('referral-details', ['record' => $record->id]))
                    ->requiresConfirmation(false),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                //DeleteBulkAction::make(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            //'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'referral-details' => Pages\ReferralDetails::route('/{record}/referrals'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email'];
    }

    public static function getWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerStatsOverview::class,
        ];
    }
}
