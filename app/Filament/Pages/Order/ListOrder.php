<?php

namespace App\Filament\Pages\Order;

use App\Models\Order;
use App\Models\Invoice;

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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

    public $dailyReportDate;
    public $monthlyReportMonth;
    public $driverSheetDate;
    public $driverList;

    public $dateFilter = 'today';

    public function mount(): void
    {
        $this->dailyReportDate = Carbon::today()->format('Y-m-d');
        $this->monthlyReportMonth = Carbon::today()->format('Y-m');
        $this->driverSheetDate = Carbon::today()->format('Y-m-d');
        $this->driverList = null;
    }

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
            ->columns([
                TextColumn::make('order_no')
                    ->label('Order No')
                    ->sortable()
                    ->url(fn(Order $record): string => $record->order_type === 'meal_plan' 
                        ? "/backend/meal-plans/{$record->id}" 
                        : "/backend/orders/{$record->id}")
                    ->color('primary'),
                TextColumn::make('order_type')
                    ->label('Order Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->getStateUsing(function (Order $record): string {
                        $delivery = $record->getDelivery();
                        return $delivery && $delivery->delivery_date 
                            ? $delivery->delivery_date->format(config('app.date_format')) 
                            : '';
                    })
                    ->sortable(false),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric(2, '.', ',')
                    ->prefix('RM ')
                    ->sortable(),
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
                TextColumn::make('driver_name')
                    ->label('Driver')
                    ->getStateUsing(function (Order $record): string {
                        $delivery = $record->getDelivery();
                        
                        if (!$delivery || !$delivery->driver_id) {
                            return '';
                        }

                        return $delivery->driver->name;
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(true),
                TextColumn::make('arrival_time')
                    ->label('Arrival Time')
                    ->getStateUsing(function (Order $record): string {
                        $delivery = $record->getDelivery();
                        if (!$delivery || !$delivery->arrival_time) {
                            return '';
                        }
                        return date(config('app.time_format'), strtotime($delivery->arrival_time));
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(true),
                    //->toggledHiddenByDefault(),
                TextColumn::make('created_at')
                    ->label('Ordered On')
                    ->dateTime(config('app.date_format'))
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime(config('app.datetime_format'))
                    ->sortable()
                    ->toggleable(true)
                    ->toggledHiddenByDefault(),
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
                    ->toggleable(true)
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
                                    ->required(fn(callable $get): bool => (string)$get('payment_status_id') === 4)
                                    ->default(function (Order $record): ?int {
                                        return $record->payment_method_id;
                                    })
                                    ->disabled(fn(callable $get) => (string)$get('payment_status_id') === 3)
                            ])
                            ->modalSubmitActionLabel('Save')
                            ->action(function (Order $record, array $data): void {
                                $data['payment_method_id'] = (string)$data['payment_status_id'] === 3 ? null : $data['payment_method_id'];
                                $record->update([
                                    'payment_status_id' => $data['payment_status_id'],
                                    'payment_method_id' => $data['payment_method_id'],
                                ]);
                            })
                            ->icon('heroicon-m-pencil-square')
                    ),
                TextColumn::make('invoice.invoice_no')
                    ->label('Invoice')
                    ->formatStateUsing(function (Order $record): string {
                        return $record->invoice ? $record->invoice->invoice_no : 'No Invoice';
                    })
                    ->searchable()
                    ->sortable()
                    ->action(
                        TableAction::make('editInvoice')
                            ->form([
                                TextInput::make('invoice_no')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->default(function (Order $record): ?string {
                                        return $record->invoice?->invoice_no ?? str_pad($record->id, config('app.order_id_padding'), '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('ref_no')
                                    ->label('Reference Number')
                                    ->default(function (Order $record): ?string {
                                        return $record->invoice?->ref_no;
                                    }),
                                TextInput::make('billing_name')
                                    ->label('Name')
                                    ->required()
                                    ->default(function (Order $record): ?string {
                                        return $record->invoice?->billing_name;
                                    }),
                                Textarea::make('billing_address')
                                    ->label('Billing Address')
                                    ->required()
                                    ->rows(6)
                                    ->default(function (Order $record): ?string {
                                        return $record->invoice?->billing_address;
                                    })
                            ])
                            ->action(function (Order $record, array $data): void {
                                $invoice = $record->invoice;
                                if (!$invoice) {
                                    $invoice = new Invoice();
                                    $invoice->order_id = $record->id;
                                    $invoice->issue_date = now();
                                    $invoice->due_date = now()->addDays(30);
                                }
                                
                                $invoice->invoice_no = $data['invoice_no'];
                                $invoice->ref_no = $data['ref_no'];
                                $invoice->billing_name = $data['billing_name'];
                                $invoice->billing_address = $data['billing_address'];
                                $invoice->save();
                                
                                // Show success notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Invoice saved successfully')
                                    ->success()
                                    ->send();
                            })
                    )
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Select::make('range_type')
                            ->label('Date Range')
                            ->options([
                                'all' => 'All Orders',
                                'daily' => "Daily Order",
                                'this_week' => "This week's Order",
                                'this_month' => "This month's Order",
                                'custom' => 'Custom Range'
                            ])
                            //->default('daily')
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
                            ->afterOrEqual('start_date')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $rangeType = $data['range_type'] ?? 'all';

                        if ($rangeType === 'all' || !$rangeType) {
                            return $query;
                        }

                        return match ($rangeType) {
                            'daily' => $query->when(
                                $data['daily_date'],
                                fn(Builder $query) => $query->whereHas('deliveries', function($q) use ($data) {
                                    $q->whereDate('delivery_date', Carbon::parse($data['daily_date']));
                                })
                            ),
                            'this_week' => $query->whereHas('deliveries', function($q) {
                                $q->whereBetween('delivery_date', [
                                    Carbon::now()->startOfWeek(),
                                    Carbon::now()->endOfWeek()
                                ]);
                            }),
                            'this_month' => $query->whereHas('deliveries', function($q) {
                                $q->whereBetween('delivery_date', [
                                    Carbon::now()->startOfMonth(),
                                    Carbon::now()->endOfMonth()
                                ]);
                            }),
                            'custom' => $query->when(
                                $data['start_date'] && $data['end_date'],
                                fn(Builder $query) => $query->whereHas('deliveries', function($q) use ($data) {
                                    $q->whereBetween('delivery_date', [
                                        Carbon::parse($data['start_date'])->startOfDay(),
                                        Carbon::parse($data['end_date'])->endOfDay()
                                    ]);
                                })
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
                            'daily' => isset($data['daily_date'])
                                ? 'Daily Orders: ' . Carbon::parse($data['daily_date'])->format('M j, Y')
                                : 'Daily Orders',
                            'this_week' => "This Week's Orders",
                            'this_month' => "This Month's Orders",
                            'custom' => isset($data['start_date'], $data['end_date'])
                                ? 'Custom Range: ' . Carbon::parse($data['start_date'])->format('M j') . ' - ' . Carbon::parse($data['end_date'])->format('M j, Y')
                                : 'Custom Range',
                            default => null
                        };
                    }),
                SelectFilter::make('order_type')
                    ->label('Order Type')
                    ->options([
                        'single' => 'Single',
                        'meal_plan' => 'Meal Plan',
                    ]),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ]),
                SelectFilter::make('payment_status_id')
                    ->label('Payment')
                    ->options([
                        3 => 'Unpaid',
                        4 => 'Paid',
                    ]),
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(\App\Models\Driver::where('status_id', 1)->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('deliveries', function (Builder $query) use ($value) {
                                $query->where('driver_id', $value);
                            })
                        );
                    }),
            ])
            ->actions([
                TableAction::make('print_invoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn(Order $record): string => "/backend/orders/print-invoice/{$record->id}")
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->url(fn(Order $record): string => $record->order_type === 'meal_plan' 
                        ? "/backend/meal-plans/{$record->id}/edit" 
                        : "/backend/orders/{$record->id}/edit"),
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
            ->defaultSort('created_at', 'desc');
    }

    protected function query(): Builder
    {
        return Order::query()
            ->with(['invoice', 'customer', 'deliveries.driver'])
            //->where('order_type', 'single')
            ->whereIn('status_id', [1, 2]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('New Order')
                ->url('/' . config('filament.path', 'backend') . '/orders/create')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function get()
    {
        return [
            '/' . config('filament.path', 'backend') . '/orders' => 'Orders',
            '' => 'Manage Orders',
        ];
    }

    private function getFilterParams(): array
    {
        $params = [];

        $search = $this->getTableSearch();
        if ($search) {
            $params['search'] = $search;
        }

        $dateRangeFilter = $this->getTableFilterState('date_range');
        if ($dateRangeFilter) {
            if (isset($dateRangeFilter['range_type']) && $dateRangeFilter['range_type']) {
                $params['date_range'] = $dateRangeFilter['range_type'];

                switch($dateRangeFilter['range_type']){
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
                    default:
                        break;
                }
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

        return $params;
    }

    

    public function getDriversProperty()
    {
        return \App\Models\Driver::where('status_id', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }


    public function printData()
    {
        $params = [];
        
        if (!$this->dailyReportDate || $this->dailyReportDate == '') {
            $this->dailyReportDate = Carbon::today()->format('Y-m-d');
        }

        $params['daily_date'] = $this->dailyReportDate;
        $params['date_range'] = 'daily';
        
        $url = route('admin.order.print_data', $params);
        
        $this->js("window.open('$url', '_blank')");
    }

    public function PrintDailyBankSalesReport()
    {
        $params = [];
        
        if (!$this->dailyReportDate || $this->dailyReportDate == '') {
            $this->dailyReportDate = Carbon::today()->format('Y-m-d');
        }

        $params['daily_date'] = $this->dailyReportDate;
        $params['date_range'] = 'daily';
        
        $url = route('admin.report.print_daily_bank_sales_report', $params);
        
        $this->js("window.open('$url', '_blank')");
    }

    public function PrintDailyOrderQuantityReport()
    {
        $params = [];
        
        if (!$this->dailyReportDate || $this->dailyReportDate == '') {
            $this->dailyReportDate = Carbon::today()->format('Y-m-d');
        }

        $params['daily_date'] = $this->dailyReportDate;
        $params['date_range'] = 'daily';
        
        $url = route('admin.report.print_daily_order_quantity_report', $params);
        
        $this->js("window.open('$url', '_blank')");
    }

    public function printMonthlySalesReport()
    {
        $params = [];

        if (!$this->monthlyReportMonth || $this->monthlyReportMonth == '') {
            $this->monthlyReportMonth = Carbon::today()->format('Y-m');
        }

        $params['month'] = $this->monthlyReportMonth;
        $params['date_range'] = 'monthly';
        
        $url = route('admin.report.print_monthly_sales_report', $params);
        
        $this->js("window.open('$url', '_blank')");
    }

    public function printDriverSheet1()
    {
        $params = [];

        if (!$this->driverSheetDate || $this->driverSheetDate == '') {
            $this->driverSheetDate = Carbon::today()->format('Y-m-d');
        }

        $params['daily_date'] = $this->driverSheetDate;
        $params['date_range'] = 'daily';
        
        if ($this->driverList) {
            $params['driver_id'] = $this->driverList;
        }
        
        $url = route('admin.order.print_driver_sheet_1', $params);
        
        $this->js("window.open('$url', '_blank')");
    }

    public function printDriverSheet2()
    {
        $params = [];

        if (!$this->driverSheetDate || $this->driverSheetDate == '') {
            $this->driverSheetDate = Carbon::today()->format('Y-m-d');
        }

        $params['daily_date'] = $this->driverSheetDate;
        $params['date_range'] = 'daily';
        
        $url = route('admin.order.print_driver_sheet_2', $params);
        
        $this->js("window.open('$url', '_blank')");
    }
}
