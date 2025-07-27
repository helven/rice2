<?php

namespace App\Filament\Pages\Order;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ListOrder extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Manage Orders';
    protected static ?string $title = 'Manage Orders';
    protected static ?string $slug = 'orders';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.order.list-order';
    
    public $dateFilter = 'today';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->headerActions([
                TableAction::make('printData')
                    ->label('Print Data')
                    ->button()
                    ->icon('heroicon-o-printer')
                    ->url(function () {
                        $params = [];

                        $search = $this->getTableSearch();
                        if ($search) {
                            $params['search'] = $search;
                        }

                        $dateRangeFilter = $this->getTableFilterState('date_range');
                        if ($dateRangeFilter) {
                            if (isset($dateRangeFilter['range_type']) && $dateRangeFilter['range_type']) {
                                $params['date_range'] = $dateRangeFilter['range_type'];
                            }
                            if (isset($dateRangeFilter['start_date']) && $dateRangeFilter['start_date']) {
                                $params['start_date'] = $dateRangeFilter['start_date'];
                            }
                            if (isset($dateRangeFilter['end_date']) && $dateRangeFilter['end_date']) {
                                $params['end_date'] = $dateRangeFilter['end_date'];
                            }
                        }

                        $statusFilter = $this->getTableFilterState('payment_status_id');
                        if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                            $params['payment_status_id'] = $statusFilter['value'];
                        }

                        $statusFilter = $this->getTableFilterState('status_id');
                        if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                            $params['status_id'] = $statusFilter['value'];
                        }

                        $customerFilter = $this->getTableFilterState('customer_id');
                        if ($customerFilter && isset($customerFilter['value']) && $customerFilter['value']) {
                            $params['customer_id'] = $customerFilter['value'];
                        }

                        $driverFilter = $this->getTableFilterState('driver_id');
                        if ($driverFilter && isset($driverFilter['value']) && $driverFilter['value']) {
                            $params['driver_id'] = $driverFilter['value'];
                        }

                        return route('admin.order.print_data', $params);
                    }, true),
                
                TableAction::make('printDriverSheet1')
                    ->label('Print Driver Sheet 1')
                    ->button()
                    ->icon('heroicon-o-printer')
                    ->url(function () {
                         $params = [];

                        $search = $this->getTableSearch();
                        if ($search) {
                            $params['search'] = $search;
                        }

                        $dateRangeFilter = $this->getTableFilterState('date_range');
                        if ($dateRangeFilter) {
                            if (isset($dateRangeFilter['range_type']) && $dateRangeFilter['range_type']) {
                                $params['date_range'] = $dateRangeFilter['range_type'];
                            }
                            if (isset($dateRangeFilter['start_date']) && $dateRangeFilter['start_date']) {
                                $params['start_date'] = $dateRangeFilter['start_date'];
                            }
                            if (isset($dateRangeFilter['end_date']) && $dateRangeFilter['end_date']) {
                                $params['end_date'] = $dateRangeFilter['end_date'];
                            }
                        }

                        $statusFilter = $this->getTableFilterState('payment_status_id');
                        if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                            $params['payment_status_id'] = $statusFilter['value'];
                        }

                        $statusFilter = $this->getTableFilterState('status_id');
                        if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                            $params['status_id'] = $statusFilter['value'];
                        }

                        $customerFilter = $this->getTableFilterState('customer_id');
                        if ($customerFilter && isset($customerFilter['value']) && $customerFilter['value']) {
                            $params['customer_id'] = $customerFilter['value'];
                        }

                        $driverFilter = $this->getTableFilterState('driver_id');
                        if ($driverFilter && isset($driverFilter['value']) && $driverFilter['value']) {
                            $params['driver_id'] = $driverFilter['value'];
                        }

                        return route('admin.order.print_driver_sheet_1', $params);
                    }, true),
            ])
            ->columns([
                TextColumn::make('formatted_id')
                    ->label('Order No')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric(2, '.', ',')
                    ->prefix('RM ')
                    ->sortable(),
                TextColumn::make('payment_status.label')
                    ->label('Payment')
                    ->formatStateUsing(function (Order $record): string {
                        $status = $record->payment_status->label ?? '';
                        $method = $record->payment_method->label ?? '';
                        return $method ? "{$status} <span class='text-gray-950'>({$method})</span>" : $status;
                    })
                    ->html()
                    ->searchable()
                    ->sortable()
                    ->color(function (Order $record): string {
                        if ($record->payment_status_id === 4) return 'success';
                        if ($record->payment_status_id === 3) return 'warning';
                        return 'gray';
                    })
                    ->action(
                        TableAction::make('editPaymentStatus')
                            ->form([
                                Select::make('payment_status_id')
                                    ->label('Payment Status')
                                    ->native()
                                    ->selectablePlaceholder(false)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ((string)$state === 3) {
                                            $set('payment_method_id', null);
                                        }
                                    })
                                    ->options([
                                        3 => 'Unpaid',
                                        4 => 'Paid',
                                    ])
                                    ->default(function (Order $record): int {
                                        return $record->payment_status_id;
                                    }),
                                Select::make('payment_method_id')
                                    ->label('Payment Method')
                                    ->native()
                                    ->placeholder('Select Payment Method')
                                    ->relationship('payment_method', 'label')
                                    ->required(fn (callable $get): bool => (string)$get('payment_status_id') === 4)
                                    ->default(function (Order $record): ?int {
                                        return $record->payment_method_id;
                                    })
                                    ->disabled(fn (callable $get) => (string)$get('payment_status_id') === 3)
                            ])
                            ->action(function (Order $record, array $data): void {
                                $data['payment_method_id'] = (string)$data['payment_status_id'] === 3 ? null : $data['payment_method_id'];
                                $record->update([
                                    'payment_status_id' => $data['payment_status_id'],
                                    'payment_method_id' => $data['payment_method_id'],
                                ]);
                            })
                            ->icon('heroicon-m-pencil-square')
                    ),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (Order $record): string {
                        if ($record->status_id === 1) return 'success';
                        if ($record->status_id === 2) return 'warning';
                        return 'gray';
                    })
                    ->toggleable(true),
                TextColumn::make('created_at')
                    ->label('Ordered On')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(true)
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Select::make('range_type')
                            ->label('Date Range')
                            ->options([
                                'all' => 'All Orders',
                                'today' => "Today's Order",
                                'week' => "This week's Order",
                                'month' => "This month's Order",
                                'custom' => 'Custom Range'
                            ])
                            ->default('today')
                            ->placeholder(false)
                            ->selectablePlaceholder(false)
                            ->live(),
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
                            ->visible(fn (callable $get) => $get('range_type') === 'custom')
                            ->required(fn (callable $get) => $get('range_type') === 'custom'),
                        DatePicker::make('end_date')
                            ->label('To Date')
                            ->extraAttributes([
                                'class' => 'custom-date-picker to-date-field',
                                'id' => 'txt_EndDate'
                            ])
                            ->visible(fn (callable $get) => $get('range_type') === 'custom')
                            ->required(fn (callable $get) => $get('range_type') === 'custom')
                            ->afterOrEqual('start_date')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $rangeType = $data['range_type'] ?? 'all';
                        
                        if ($rangeType === 'all' || !$rangeType) {
                            return $query;
                        }
                        
                        return match ($rangeType) {
                            'today' => $query->whereDate('delivery_date', Carbon::today()),
                            'week' => $query->whereBetween('delivery_date', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek()
                            ]),
                            'month' => $query->whereBetween('delivery_date', [
                                Carbon::now()->startOfMonth(),
                                Carbon::now()->endOfMonth()
                            ]),
                            'custom' => $query->when(
                                $data['start_date'] && $data['end_date'],
                                fn (Builder $query) => $query->whereBetween('delivery_date', [
                                    Carbon::parse($data['start_date'])->startOfDay(),
                                    Carbon::parse($data['end_date'])->endOfDay()
                                ])
                            ),
                            default => $query
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $rangeType = $data['range_type'] ?? 'all';
                        
                        if ($rangeType === 'all' || !$rangeType) {
                            return null;
                        }
                        
                        return match ($rangeType) {
                            'today' => "Today's Orders",
                            'week' => "This Week's Orders",
                            'month' => "This Month's Orders",
                            'custom' => isset($data['start_date'], $data['end_date']) 
                                ? 'Custom Range: ' . Carbon::parse($data['start_date'])->format('M j') . ' - ' . Carbon::parse($data['end_date'])->format('M j, Y')
                                : 'Custom Range',
                            default => null
                        };
                    }),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ]),
                SelectFilter::make('payment_status_id')
                    ->label('Payment')
                    ->options([
                        3 => 'Paid',
                        4 => 'Unpaid',
                    ]),
                SelectFilter::make('customer_id')
                        ->relationship('customer', 'name'),
                SelectFilter::make('driver_id')
                    ->relationship('driver', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Order $record): string => "/backend/orders/{$record->id}/edit"),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        $record->update(['status_id' => 99]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete')
                        ->label('Delete selected')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status_id' => 99]);
                            });
                        })
                ]),
            ])
            ->defaultSort('delivery_date', 'desc');
    }

    protected function query(): Builder
    {
        return Order::query()->whereIn('status_id', [1, 2]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('New Order')
                ->url('/'.config('filament.path', 'backend').'/orders/create')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function get()
    {
        return [
            '/'.config('filament.path', 'backend').'/orders' => 'Orders',
            '' => 'Manage Orders',
        ];
    }
}
