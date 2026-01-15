<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\OperationalHour;
use App\Rules\DateWithinOperationalHoursRule;
use App\Rules\TimeWithinOperationalHoursRule;
use App\Services\InvoiceService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $slug = 'appointments';

    //protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): string
    {
        return __('Appointments');
    }

    public static function getLabel(): ?string
    {
        return __('Appointment');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Appointments');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()->schema([

                    Select::make('status_id')
                        ->relationship('status', 'title')
                        ->options(\App\Models\AppointmentStatus::all()->pluck('title', 'id')->toArray())
                        ->searchable()
                        ->default(1)
                        ->placeholder('Select Status')
                        ->preload(),

                    Select::make('service_provider_id')
                        ->relationship('serviceProvider', 'name')
                        ->searchable()
                        ->placeholder('Select Service Provider')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? $record->phone_number)
                        ->preload()
                        ->required()
                        ->live(),

                    Select::make('customer_id')
                        ->relationship('customer', 'first_name')
                        ->options(\App\Models\Customer::get()->mapWithKeys(function ($customer) {
                            return [$customer->id => $customer->first_name.' '.$customer->last_name];
                        })->toArray())
                        ->searchable()
                        ->required()
                        ->preload(),

                    Section::make(__('Services'))->schema([
                        Repeater::make('AppointmentServices')
                            ->disabled(fn (callable $get) => $get('service_provider_id') === null)
                            ->relationship()
                            ->live()
                            ->mutateRelationshipDataBeforeCreateUsing(fn ($get, $data) => self::concludeEndTime($get,
                                $data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn ($get, $data) => self::concludeEndTime($get,
                                $data))
                            ->schema([
                                Select::make('service_id')
                                    ->relationship('service', 'title')
                                    ->options(fn (callable $get) => \App\Models\Service::whereHas('attachedServices',
                                        function ($query) use ($get) {
                                            $query->where('service_provider_id', $get('../../service_provider_id'));
                                        })->get()->pluck('title', 'id'))
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->title ?? ' ')
                                    ->live()
                                    ->required(),

                                Select::make('employee_id')
                                    ->relationship('employee', 'name')
                                    ->options(fn (callable $get) => \App\Models\Employee::where('provider_id', $get('../../service_provider_id'))
                                        ->whereHas('services', function ($query) use ($get) {
                                            $query->where('services.id', $get('service_id'));
                                        })
                                        ->get()
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->label('Employee (Optional)')
                                    ->helperText('Select employee for enterprise providers')
                                    ->hidden(fn (callable $get) => $get('service_id') === null),

                                DatePicker::make('date')
                                    ->required()
                                    ->helperText(fn ($get) => self::getAvailableServiceDays($get))
                                    ->native(false)
                                    ->live()
                                    ->rule(fn ($get) => new DateWithinOperationalHoursRule($get('service_id'),
                                        $get('../../service_provider_id'))
                                    )
                                    ->after('yesterday'),

                                TimePicker::make('start_time')
                                    ->seconds(false)
                                    ->live()
                                    ->required()
                                    ->helperText(fn ($get) => self::getAvailableServiceHours($get))
                                    ->rule(fn ($get) => new TimeWithinOperationalHoursRule($get('date'),
                                        $get('service_id'),
                                        $get('../../service_provider_id'))
                                    ),

                                Select::make('delivery_type_id')
                                    ->hidden(fn (callable $get) => $get('service_id') === null)
                                    ->label(__('Delivery Type'))
                                    ->preload()
                                    ->options(fn ($get) => \App\Models\AttachedService::where('service_id',
                                        $get('service_id'))->where('service_provider_id',
                                            $get('../../service_provider_id'))->first()?->deliveryTypes()->get()->pluck('title',
                                                'id')->toArray() ?? []
                                    )
                                    ->searchable()
                                    ->live()
                                    ->required(),

                                TextInput::make('number_of_beneficiaries')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(6)
                                    ->default(1),
                            ]),
                    ]),
                ])->columns(2),

                Section::make('Payment & Invoice Information')->schema([

                    Select::make('payment_status')
                        ->options([
                            'unpaid' => 'Unpaid',
                            'partially_paid' => 'Partially Paid',
                            'paid' => 'Paid',
                        ])
                        ->disabled()
                        ->label('Overall Payment Status'),

                    Select::make('payment_method_id')
                        ->relationship('paymentMethod', 'name')
                        ->disabled()
                        ->label('Payment Method'),

                    TextInput::make('total')
                        ->prefix('SAR')
                        ->numeric()
                        ->disabled()
                        ->label('Total Amount'),

                    TextInput::make('amount_due')
                        ->prefix('SAR')
                        ->numeric()
                        ->disabled()
                        ->label('Amount Due'),

                    TextInput::make('total_payed')
                        ->prefix('SAR')
                        ->numeric()
                        ->disabled()
                        ->label('Total Paid'),

                    TextInput::make('discount')
                        ->prefix('SAR')
                        ->numeric()
                        ->disabled()
                        ->label('Discount Applied'),

                ])->columns(2),

                Section::make('Deposit Payment Details')
                    ->schema([
                        TextInput::make('deposit_amount')
                            ->prefix('SAR')
                            ->numeric()
                            ->disabled()
                            ->label('Deposit Amount')
                            ->placeholder('No deposit required'),

                        Select::make('deposit_payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->disabled()
                            ->label('Deposit Status')
                            ->placeholder('N/A'),

                        Select::make('deposit_payment_method_id')
                            ->relationship('depositPaymentMethod', 'name')
                            ->disabled()
                            ->label('Deposit Payment Method')
                            ->placeholder('N/A'),
                    ])
                    ->columns(3)
                    ->visible(fn (?Appointment $record): bool => $record?->deposit_amount > 0),

                Section::make('Remaining Payment Details')
                    ->schema([
                        TextInput::make('remaining_amount')
                            ->prefix('SAR')
                            ->numeric()
                            ->disabled()
                            ->label('Remaining Amount')
                            ->placeholder('No remaining amount'),

                        Select::make('remaining_payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->disabled()
                            ->label('Remaining Status')
                            ->placeholder('N/A'),

                        Select::make('remaining_payment_method_id')
                            ->relationship('remainingPaymentMethod', 'name')
                            ->disabled()
                            ->label('Remaining Payment Method')
                            ->placeholder('N/A'),
                    ])
                    ->columns(3)
                    ->visible(fn (?Appointment $record): bool => $record?->remaining_amount > 0),

                Section::make('Employee Details')
                    ->schema([
                        Placeholder::make('employee_info')
                            ->label('Assigned Employees')
                            ->content(function (?Appointment $record): string {
                                if (!$record) {
                                    return 'N/A';
                                }

                                $employees = $record->appointmentServices()
                                    ->with('employee', 'service')
                                    ->get()
                                    ->filter(fn($appointmentService) => $appointmentService->employee !== null)
                                    ->map(fn($appointmentService) => $appointmentService->service->title . ': ' . $appointmentService->employee->name)
                                    ->join('<br>');

                                return $employees ?: 'No employees assigned (Freelancer provider)';
                            })
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose prose-sm']),
                    ])
                    ->visible(fn (?Appointment $record): bool => $record !== null),

                Section::make('Invoice Details')
                    ->schema([
                        Placeholder::make('invoice_number')
                            ->label('Invoice Number')
                            ->content(fn (?Appointment $record): string => $record?->invoice?->invoice_number ?? 'No invoice generated'),

                        Placeholder::make('invoice_date')
                            ->label('Invoice Date')
                            ->content(fn (?Appointment $record): string => $record?->invoice?->invoice_date?->format('Y-m-d') ?? '-'),

                        Placeholder::make('invoice_total')
                            ->label('Invoice Total (with VAT)')
                            ->content(fn (?Appointment $record): string => $record?->invoice ? 'SAR ' . number_format($record->invoice->total_amount, 2) : '-'),

                        Placeholder::make('invoice_actions')
                            ->label('Invoice Actions')
                            ->content(function (?Appointment $record) {
                                if ($record?->payment_status === 'paid') {
                                    if ($record->invoice) {
                                        return new HtmlString('<a href="' . $record->invoice->getPdfUrl() . '" target="_blank" class="text-primary-600 hover:text-primary-900">View Invoice PDF</a>');
                                    } else {
                                        return 'Invoice can be generated';
                                    }
                                }
                                return 'Invoice available only for paid appointments';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (?Appointment $record): bool => $record !== null),

                Section::make()->schema([

                    Placeholder::make('created_at')
                        ->label('Created Date')
                        ->content(fn (?Appointment $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label('Last Modified Date')
                        ->content(fn (?Appointment $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointmentServices.service.title')
                    ->searchable()
                    ->badge()
                    ->label('Service'),

                TextColumn::make('appointmentServices.employee.name')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->default('N/A')
                    ->label('Employee'),

                TextColumn::make('serviceProvider.name')
                    ->searchable()
                    ->label('Service Provider'),

                TextColumn::make('customer.first_name')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->customer->first_name.' '.$record->customer->last_name)
                    ->label('Customer'),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    })
                    ->label('Payment Status'),

                TextColumn::make('status.title')
                    ->badge()
                    ->label('Status'),

                TextColumn::make('total')
                    ->money('SAR')
                    ->sortable()
                    ->label('Total Amount'),

            ])
            ->actions([
                ActionGroup::make([
                    Action::make('download_invoice')
                        ->label('Download Invoice')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->visible(fn (Appointment $record): bool => $record->payment_status === 'paid')
                        ->action(function (Appointment $record) {
                            try {
                                // Check if invoice exists
                                if (!$record->invoice) {
                                    // Generate invoice if it doesn't exist
                                    $invoiceService = new InvoiceService();
                                    $invoice = $invoiceService->generateInvoice($record);

                                    Notification::make()
                                        ->title('Invoice Generated')
                                        ->body('Invoice has been generated successfully.')
                                        ->success()
                                        ->send();
                                } else {
                                    $invoice = $record->invoice;
                                }

                                // Return the PDF download
                                $invoiceService = new InvoiceService();
                                return $invoiceService->downloadInvoice($invoice);

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Failed to generate/download invoice: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('view_invoice')
                        ->label('View Invoice')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(fn (Appointment $record): bool => $record->invoice !== null)
                        ->url(fn (Appointment $record): string => $record->invoice ? $record->invoice->getPdfUrl() : '#')
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                BulkAction::make('generate_invoices')
                    ->label('Generate Invoices')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Invoices')
                    ->modalDescription('This will generate invoices for all selected paid appointments that don\'t have invoices yet.')
                    ->action(function (Collection $records) {
                        $invoiceService = new InvoiceService();
                        $generated = 0;
                        $skipped = 0;
                        $errors = 0;

                        foreach ($records as $appointment) {
                            if ($appointment->payment_status !== 'paid') {
                                $skipped++;
                                continue;
                            }

                            if ($appointment->invoice) {
                                $skipped++;
                                continue;
                            }

                            try {
                                $invoiceService->generateInvoice($appointment);
                                $generated++;
                            } catch (\Exception $e) {
                                $errors++;
                            }
                        }

                        Notification::make()
                            ->title('Invoice Generation Complete')
                            ->body("Generated: {$generated}, Skipped: {$skipped}, Errors: {$errors}")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    private static function concludeEndTime($get, $data)
    {
        $day_of_week = Carbon::parse($data['date'])->format('l');
        $ops_hours = OperationalHour::where('service_provider_id', $get('service_provider_id'))
            ->where('service_id', $data['service_id'])
            ->where('day_of_week', $day_of_week)
            ->first()
            ->duration_in_minutes;

        $data['end_time'] = Carbon::parse($data['start_time'])->addMinutes($ops_hours)->format('H:i:s');

        return $data;
    }

    private static function getAvailableServiceDays($get)
    {
        $ops_hours = OperationalHour::where('service_provider_id', $get('../../service_provider_id'))
            ->where('service_id', $get('service_id'))
            ->get()->pluck('day_of_week')->toArray();

        return 'Available on '.implode(', ', $ops_hours);
    }

    private static function getAvailableServiceHours($get)
    {
        $day_of_week = Carbon::parse($get('date'))->format('l');
        $ops_hours = OperationalHour::where('service_provider_id', $get('../../service_provider_id'))
            ->where('service_id', $get('service_id'))
            ->where('day_of_week', $day_of_week)
            ->first();

        if ($ops_hours) {
            $start_time = Carbon::parse($ops_hours->start_time)->format('h:i A');
            $end_time = Carbon::parse($ops_hours->end_time)->format('h:i A');

            return 'Available from '.$start_time.' to '.$end_time;
        }

        return 'Not available on this day';

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            //'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
