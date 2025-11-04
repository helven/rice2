<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Models\Order;
use App\Filament\Resources\DriverResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action as TableAction;
use Carbon\Carbon;

class ListDropOff extends ListRecords
{
    protected static string $resource = DriverResource::class;
    protected static ?string $navigationLabel = 'Drop Off';
    protected static ?string $title = 'Drop Off';

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

                    switch($dateRangeFilter['range_type']){
                        case 'daily':
                            if (isset($dateRangeFilter['daily_date']) && $dateRangeFilter['daily_date']) {
                                $params['daily_date'] = $dateRangeFilter['daily_date'];
                            }
                            break;
                        case 'custom':
                            if (isset($dateRangeFilter['start_date']) && $dateRangeFilter['start_date']) {
                                $params['start_date'] = $dateRangeFilter['start_date'];
                            }
                            if (isset($dateRangeFilter['end_date']) && $dateRangeFilter['end_date']) {
                                $params['end_date'] = $dateRangeFilter['end_date'];
                            }
                            break;
                        default:
                            break;
                    }
                }
            }

            $orderRangeFilter = $table->getTableFilterState('order_id_range');
            if ($orderRangeFilter) {
                if (isset($orderRangeFilter['order_id_from']) && $orderRangeFilter['order_id_from']) {
                    $params['order_id_from'] = $orderRangeFilter['order_id_from'];
                }
                if (isset($orderRangeFilter['order_id_to']) && $orderRangeFilter['order_id_to']) {
                    $params['order_id_to'] = $orderRangeFilter['order_id_to'];
                }
            }

            $statusFilter = $table->getTableFilterState('status_id');
            if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                $params['status_id'] = $statusFilter['value'];
            }

            $customerFilter = $table->getTableFilterState('customer');
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
            ->recordUrl(fn(\App\Models\Delivery $record): string => "/backend/orders/{$record->order->id}/edit")
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
                TextColumn::make('order.formatted_id')
                    ->label('Order No')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        if (str_starts_with($search, '0')) {
                            $searchWithoutLeadingZeros = ltrim($search, '0');
                            return $query->whereHas('order', function($q) use ($searchWithoutLeadingZeros) {
                                $q->where('id', '=', $searchWithoutLeadingZeros);
                            });
                        }
                        return $query->whereHas('order', function($q) use ($search) {
                            $q->where('id', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date(config('app.date_format'))
                    ->sortable(),
                TextColumn::make('dropoff_time')
                    ->label('DropOff Time')
                    ->state(function ($record) {
                        return $record->dropoff_time ? $record->dropoff_time : 'NULL';
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
                                    ->seconds(false),
                            ])
                            ->action(function (\App\Models\Delivery $record, array $data): void {
                                $record->update(['dropoff_time' => $data['dropoff_time']]);
                            })
                            ->fillForm(fn(\App\Models\Delivery $record): array => [
                                'dropoff_time' => $record->dropoff_time,
                            ])
                    ),
                TextColumn::make('arrival_time')
                    ->label('Arrival Time')
                    ->getStateUsing(function (\App\Models\Delivery $record): string {
                        if (!$record->arrival_time) {
                            return '';
                        }
                        return date(config('app.time_format'), strtotime($record->arrival_time));
                    })
                    ->sortable(),
                TextColumn::make('order.customer.name')
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
                    ->sortable()
                    ->html(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable(false)
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Select::make('range_type')
                            ->label('Date Range')
                            ->options([
                                'all' => 'All Drop Offs',
                                'daily' => "Daily Drop Offs",
                                'this_week' => "This week's Drop Offs",
                                'this_month' => "This month's Drop Offs",
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
                SelectFilter::make('order_type')
                    ->label('Order Type')
                    ->options([
                        'single' => 'Single Order',
                        'meal_plan' => 'Meal Plan',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('order', function (Builder $query) use ($value) {
                                $query->where('order_type', $value);
                            })
                        );
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
                                fn(Builder $query, $value): Builder => $query->whereHas('order', function($q) use ($value) {
                                    $q->where('id', '>=', $value);
                                }),
                            )
                            ->when(
                                $data['order_id_to'],
                                fn(Builder $query, $value): Builder => $query->whereHas('order', function($q) use ($value) {
                                    $q->where('id', '<=', $value);
                                }),
                            );
                    }),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('order', function (Builder $query) use ($value) {
                                $query->where('status_id', $value);
                            })
                        );
                    }),
                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(\App\Models\Driver::where('status_id', 1)->pluck('name', 'id')),
                SelectFilter::make('customer')
                    ->relationship('order.customer', 'name')
            ])
            ->defaultSort('id', 'desc');
    }

    protected function query(): Builder
    {
        return \App\Models\Delivery::query()
            ->with(['order.customer', 'address', 'driver'])
            ->whereHas('order', function($q) {
                $q->whereIn('status_id', [1, 2]);
            });
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
