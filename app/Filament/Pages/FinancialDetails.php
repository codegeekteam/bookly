<?php

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Models\ServiceProvider;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class FinancialDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.financial-details';

    public static function getLabel(): ?string
    {
        return __('Financial Details');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Financial Details');
    }

    public static function getNavigationLabel(): string
    {
        return __('Financial Details');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ServiceProvider::query()->with('appointments'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(isIndividual: true),
                TextColumn::make('appointments_count')
                    ->label(__('Appointment Count'))
                    ->state(function ($record, $livewire) {
                        $filter = $livewire->tableFilters['date_range'] ?? [];
                        $query = $record->appointments()->where('status_id', AppointmentStatus::Completed->value);

                        if (! empty($filter['date_range'])) {
                            $this->applyDateRange($query, $filter);
                        }

                        return $query->count();
                    }),
                TextColumn::make('revenue')
                    ->label(__('Revenue'))
                    ->suffix(__('SAR'))
                    ->state(function ($record, $livewire) {
                        $filter = $livewire->tableFilters['date_range'] ?? [];
                        $query = $record->appointments()->where('status_id', AppointmentStatus::Completed->value);

                        if (! empty($filter['date_range'])) {
                            $this->applyDateRange($query, $filter);
                        }

                        return $query->sum('total_payed');
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->indicateUsing(fn ($data) => $this->displayIndicator($data))
                    ->form([
                        Select::make('date_range')
                            ->live()
                            ->options([
                                'today' => __('Today'),
                                'this_week' => __('This Week'),
                                'this_month' => __('This Month'),
                                'custom' => __('Custom'),
                            ])
                            ->required(),
                        DatePicker::make('created_from')
                            ->label(__('From'))
                            ->visible(fn ($get) => $get('date_range') === 'custom')
                            ->live(),
                        DatePicker::make('created_until')
                            ->label(__('Until'))
                            ->after('created_from')
                            ->visible(fn ($get) => $get('date_range') === 'custom')
                            ->live(),
                    ])
                    ->label(__('Date Range')),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([]);
    }

    private function applyDateRange($query, $filter): void
    {
        $today = Carbon::today();

        switch ($filter['date_range']) {
            case 'today':
                $query->whereDate('changed_status_at', $today);
                break;
            case 'this_week':
                $query->whereBetween('changed_status_at', [$today->copy()->startOfWeek(Carbon::SATURDAY), $today->copy()->endOfWeek(Carbon::FRIDAY)]);
                break;
            case 'this_month':
                $query->whereBetween('changed_status_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
                break;
            case 'custom':
                $query->when($filter['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('changed_status_at', '>=', $date))
                    ->when($filter['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('changed_status_at', '<=', $date));
                break;
        }
    }

    private function displayIndicator(array $data)
    {

        switch ($data['date_range']) {

            case 'today':
                return __('Today');
            case 'this_week':
                return __('This Week');
            case 'this_month':
                return __('This Month');
            case 'custom':
                $date_from = $data['created_from'] ? 'From '.$data['created_from'] : '';
                $date_until = $data['created_until'] ? 'To '.$data['created_until'] : '';

                return $date_from.' '.$date_until;

                break;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exports([
                ExcelExport::make()
                    ->withFilename(fn ($livewire) => str_replace(' ', '_', 'financial_details_'.strtolower($this->displayIndicator($livewire->tableFilters['date_range']))))
                    ->fromTable(),
            ]),
        ];
    }
}
