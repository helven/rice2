<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\Action as TableAction;
use Livewire\Attributes\Url;
use Carbon\Carbon;

class ListDropOff extends ListRecords
{
    protected static string $resource = DriverResource::class;
    protected static ?string $navigationLabel = 'Drop Off';
    protected static ?string $title = 'Drop Off';

    #[Url]
    public $orderIdFrom = '';

    #[Url]
    public $orderIdTo = '';

    public function table(Table $table): Table
    {
        function filterParams($table)
        {
            $params = [];

            $search = $table->getTableSearch();
            if ($search) {
                $params['search'] = $search;
            }

            $dateRangeFilter = $table->getTableFilterState('date_range');
            if ($dateRangeFilter) {
                if (isset($dateRangeFilter['range_type']) && $dateRangeFilter['range_type']) {
                    $params['date_range'] = $dateRangeFilter['range_type'];

                    switch ($dateRangeFilter['range_type']) {
                        case 'daily':
                            if (isset($dateRangeFilter['daily_date']) && $dateRangeFilter['daily_date']) {
                                $params['daily_date'] = $dateRangeFilter['daily_date'];
                            }
                            break;
                        case 'this_week':
                            if (isset($dateRangeFilter['start_date']) && $dateRangeFilter['start_date']) {
                                $params['start_date'] = $dateRangeFilter['start_date'];
                            }
                            break;
                        case 'this_month':
                            if (isset($dateRangeFilter['end_date']) && $dateRangeFilter['end_date']) {
                                $params['end_date'] = $dateRangeFilter['end_date'];
                            }
                            break;
                            defaut:
                    }
                }
            }

            $statusFilter = $table->getTableFilterState('payment_status_id');
            if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                $params['payment_status_id'] = $statusFilter['value'];
            }

            $statusFilter = $table->getTableFilterState('status_id');
            if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                $params['status_id'] = $statusFilter['value'];
            }

            $customerFilter = $table->getTableFilterState('customer_id');
            if ($customerFilter && isset($customerFilter['value']) && $customerFilter['value']) {
                $params['customer_id'] = $customerFilter['value'];
            }

            $driverFilter = $table->getTableFilterState('driver_id');
            if ($driverFilter && isset($driverFilter['value']) && $driverFilter['value']) {
                $params['driver_id'] = $driverFilter['value'];
            }

            return $params;
        }

        return $table
            ->query($this->query())
            ->recordUrl(fn(Order $record): string => "/backend/orders/{$record->id}/edit")
            ->headerActions([
                TableAction::make('printDropOff')
                    ->label('Print Drop Off')
                    ->button()
                    ->icon('heroicon-o-printer')
                    ->url(function () {
                        $params = filterParams($this);

                        return route('admin.order.print_dropoff', $params);
                    }, true),
            ])
            ->columns([
                TextColumn::make('formatted_id')
                    ->label('Order No')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        // If the search term starts with zeros, make it an exact match after stripping zeros
                        if (str_starts_with($search, '0')) {
                            $searchWithoutLeadingZeros = ltrim($search, '0');
                            return $query->where('id', '=', $searchWithoutLeadingZeros);
                        }
                        // Otherwise, do a partial match
                        return $query->where('id', 'like', "%{$search}%");
                    })
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                TextColumn::make('dropoff_time')
                    ->label('DropOff Time')
                    ->state(function ($record) {
                        return $record->dropoff_time ?: 'NULL';
                    })
                    ->formatStateUsing(function ($state): string {
                        if ($state === 'NULL') {
                            return 'Set dropoff time';
                        }
                        return \Carbon\Carbon::parse($state)->format('h:i A');
                    })
                    ->icon('heroicon-m-pencil-square')
                    ->iconPosition('before')
                    ->html()
                    ->sortable()
                    ->action(
                        TableAction::make('editDropoffTimeInline')
                            ->label('Edit Dropoff Time')
                            ->modalHeading('Edit Dropoff Time')
                            ->modalWidth('sm')
                            ->form([
                                TimePicker::make('dropoff_time')
                                    ->label('Dropoff Time')
                                    ->required()
                                    ->seconds(false), // Don't include seconds
                            ])
                            ->action(function (Order $record, array $data): void {
                                $record->update(['dropoff_time' => $data['dropoff_time']]);
                            })
                            ->fillForm(fn(Order $record): array => [
                                'dropoff_time' => $record->dropoff_time,
                            ])
                    ),
                TextColumn::make('arrival_time')
                    ->label('Arrival Time')
                    ->dateTime('H:i A')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address.name')
                    ->label('Delivery Location')
                    ->formatStateUsing(function ($record) {
                        if (!$record->address) {
                            return '';
                        }
                        return new HtmlString(
                            '<span class="font-bold">' . e($record->address->name) . '</span><br />' .
                                e($record->address->address_1) . ', ' . e($record->address->city)
                        );
                    })
                    ->html(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->toggleable(true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Select::make('range_type')
                            ->label('Date Range')
                            ->options([
                                'all' => 'All DropOffs',
                                'daily' => "Daily DropOffs",
                                'this_week' => "This week's DropOffs",
                                'this_month' => "This month's DropOffs",
                                'custom' => 'Custom Range'
                            ])
                            ->default('daily')
                            ->placeholder(false)
                            ->selectablePlaceholder(false)
                            ->live(),
                        Select::make('daily_date')
                            ->label('Select Date')
                            ->options(function () {
                                $dates = [];
                                $startOfMonth = Carbon::now()->startOfMonth();
                                $endOfMonth = Carbon::now()->endOfMonth();

                                for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                                    $dates[$date->format('Y-m-d')] = $date->format('d M Y (D)');
                                }

                                return $dates;
                            })
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->placeholder(false)
                            ->selectablePlaceholder(false)
                            ->visible(fn(callable $get) => $get('range_type') === 'daily')
                            ->required(fn(callable $get) => $get('range_type') === 'daily'),
                        //->extraAttributes([
                        //    'x-data' => '{ 
                        //        rangeType: "today",
                        //        toggleDateFields(type) {
                        //            const startField = document.querySelector(".fi-fo-field-wrp:has(.from-date-field)");
                        //            const endFie.d = document.querySelector(".fi-fo-field-wrp:has(.to-date-field)");
                        //            if (startField && endField) {
                        //                if (type === "custom") {
                        //                    startField.style.display = "block";
                        //                    endField.style.display = "block";
                        //                } else {
                        //                    startField.style.display = "none";
                        //                    endField.style.display = "none";
                        //                }
                        //            }
                        //        }
                        //    }',
                        //    'x-init' => '$nextTick(() => toggleDateFields(rangeType))',
                        //    'x-on:change' => 'rangeType = $event.target.value; toggleDateFields(rangeType)'
                        //]),
                        DatePicker::make('start_date')
                            ->label('From Date')
                            ->extraAttributes([
                                'class' => 'custom-date-picker from-date-field',
                                'id' => 'txt_StartDate'
                            ])
                            ->visible(fn(callable $get) => $get('range_type') === 'custom')
                            ->required(fn(callable $get) => $get('range_type') === 'custom'),
                        DatePicker::make('end_date')
                            ->label('To Date')
                            ->extraAttributes([
                                'class' => 'custom-date-picker to-date-field',
                                'id' => 'txt_EndDate'
                            ])
                            ->visible(fn(callable $get) => $get('range_type') === 'custom')
                            ->required(fn(callable $get) => $get('range_type') === 'custom')
                            ->afterOrEqual('start_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $rangeType = $data['range_type'] ?? 'all';

                        if ($rangeType === 'all' || !$rangeType) {
                            return $query;
                        }

                        return match ($rangeType) {
                            'daily' => $query->when(
                                $data['daily_date'],
                                fn(Builder $query) => $query->whereDate('delivery_date', Carbon::parse($data['daily_date']))
                            ),
                            'this_week' => $query->whereBetween('delivery_date', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek()
                            ]),
                            'this_month' => $query->whereBetween('delivery_date', [
                                Carbon::now()->startOfMonth(),
                                Carbon::now()->endOfMonth()
                            ]),
                            'custom' => $query->when(
                                $data['start_date'] && $data['end_date'],
                                fn(Builder $query) => $query->whereBetween('delivery_date', [
                                    Carbon::parse($data['start_date'])->startOfDay(),
                                    Carbon::parse($data['end_date'])->endOfDay()
                                ])
                            ),
                            default => $query
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $rangeType = $data['range_type'] ?? null;
                        
                        if (!$rangeType || $rangeType === 'all') {
                            return null;
                        }
                        
                        return match ($rangeType) {
                            'daily' => 'Daily: ' . ($data['daily_date'] ? Carbon::parse($data['daily_date'])->format('d M Y') : 'Today'),
                            'this_week' => 'This Week',
                            'this_month' => 'This Month',
                            'custom' => $data['start_date'] && $data['end_date'] 
                                ? 'Custom: ' . Carbon::parse($data['start_date'])->format('d M') . ' - ' . Carbon::parse($data['end_date'])->format('d M Y')
                                : 'Custom Range',
                            default => null
                        };
                    }),
                Filter::make('order_id_range')
                    ->form([
                        TextInput::make('order_id_from')
                            ->label('Order No From')
                            ->numeric()
                            ->placeholder('e.g., 3'),
                        TextInput::make('order_id_to')
                            ->label('Order No To')
                            ->numeric()
                            ->placeholder('e.g., 10'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_id_from'],
                                fn(Builder $query, $value): Builder => $query->where('id', '>=', $value),
                            )
                            ->when(
                                $data['order_id_to'],
                                fn(Builder $query, $value): Builder => $query->where('id', '<=', $value),
                            );
                    }),
                //Tables\Filters\Filter::make('todays_dropoff')
                //    ->label("Today's Drop Off")
                //    ->toggle()
                //    ->query(fn(Builder $query): Builder => $query->whereDate('delivery_date', now())),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ]),
                SelectFilter::make('driver')
                    ->relationship('driver', 'name'),
                SelectFilter::make('customer')
                    ->relationship('customer', 'name'),
            ])
            ->defaultSort('delivery_date', 'desc');
        // Removed separate actions column as requested
    }

    protected function query(): Builder
    {
        $query = Order::query()
            ->with(['customer', 'driver'])
            ->whereNotNull('dropoff_time')
            ->orderBy('delivery_date', 'desc')
            ->orderBy('dropoff_time', 'desc');

        //if (!empty($this->orderIdFrom)) {
        //    $query->where('id', '>=', $this->orderIdFrom);
        //}

        //if (!empty($this->orderIdTo)) {
        //    $query->where('id', '<=', $this->orderIdTo);
        //}

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/' . config('filament.path', 'backend') . '/drivers' => 'Drivers',
            '' => 'Drop Off',
        ];
    }
}
