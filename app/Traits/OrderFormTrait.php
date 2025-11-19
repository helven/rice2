<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Invoice;
use App\Models\Driver;
use App\Models\Area;
use App\Models\Meal;
use App\Models\AttrPaymentMethod;
use App\Models\OrderStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;

trait OrderFormTrait
{
    private function getDefaultPaymentStatusId(): int
    {
        return 3;
    }

    private function createMealQuantityField(string $name, string $label, string $orderType = 'single'): TextInput
    {
        $jsFunction = $orderType === 'meal_plan' ? 'handleMealPlanQtyChange' : 'handleMealQtyChange';

        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->default(0)
            ->minValue(0)
            ->maxValue(1000)
            ->extraInputAttributes([
                'data-id' => $name,
                'data-class' => 'meal-qty',
                'min' => '0',
                'onchange' => "{$jsFunction}(this)",
                'onkeyup' => "{$jsFunction}(this)"
            ])
            ->rules(['required', 'integer', 'min:0', 'max:1000']);
    }

    protected function getCustomerAddressGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('customer_id')
                ->label('Customer Name')
                ->extraInputAttributes([
                    'data-id' => 'customer_id'
                ])
                ->placeholder('Select Customer')
                ->required()
                ->searchable()
                ->preload()
                ->getSearchResultsUsing(function (string $search) {
                    $search = trim($search);
                    if (empty($search) || strlen($search) > 100) {
                        return [];
                    }

                    $escapedSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);

                    return Customer::query()
                        ->where(function ($q) use ($escapedSearch) {
                            $q->where('name', 'like', "%{$escapedSearch}%")
                                ->orWhere('contact', 'like', "%{$escapedSearch}%");
                        })
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->options(Customer::query()->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $set('address_id', null);

                    if ($state) {
                        $customer = Customer::find($state);
                        if ($customer && $customer->payment_method_id) {
                            $set('payment_method_id', $customer->payment_method_id);
                        }
                    }
                }),

            Select::make('address_id')
                ->label('Delivery Location')
                ->extraInputAttributes([
                    'data-id' => 'address_id'
                ])
                ->placeholder('Select Company or City')
                ->required()
                ->searchable()
                ->allowHtml()
                ->live()
                ->disabled(fn(callable $get): bool => blank($get('customer_id')))
                ->options(function (callable $get) {
                    $customerId = $get('customer_id');
                    if (blank($customerId)) {
                        return [];
                    }
                    return CustomerAddressBook::query()
                        ->where('customer_id', $customerId)
                        ->where('status_id', 1)
                        ->orderBy('is_default', 'desc')
                        ->orderBy('name', 'asc')
                        ->get()
                        ->mapWithKeys(function ($address) {
                            $address->address_1 = trim($address->address_1);
                            $address->address_2 = trim($address->address_2);
                            ob_start(); ?>
                            <div class="hidden"><?php echo e($address->name) . '|' . e($address->city); ?></div>
                            <span class="font-bold"><?php echo e($address->name); ?></span>
                            <?php echo $address->is_default ? '<span class="italic text-xs text-gray-400"> (default)</span>' : ""; ?>
                            <div><?php echo e($address->address_1); ?><br />
                                <?php echo ($address->address_2) ? e($address->address_2) . '<br />' : ""; ?>
                                <?php echo e($address->postcode); ?> <?php echo e($address->city); ?>
                            </div>
                            <?php
                            $displayAddress = trim(ob_get_clean());
                            return [$address->id => $displayAddress];
                        });
                })
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state) {
                        $address = CustomerAddressBook::with(['driver', 'backup_driver', 'mall'])->find($state);
                        if ($address) {
                            // Clear existing driver data first
                            $set('driver_id', null);
                            $set('driver_route', null);
                            $set('backup_driver_id', null);

                            // Set new driver data if available
                            if ($address->driver_id) {
                                $set('driver_id', $address->driver_id);
                                if ($address->driver_route) {
                                    $set('driver_route', $address->driver_route);
                                }
                            }

                            if ($address->backup_driver_id) {
                                $set('backup_driver_id', $address->backup_driver_id);
                            }

                            // Handle payment method based on mall_id
                            if ($address->mall_id && $address->mall_id != 0) {
                                // Mall has higher priority - use mall's payment method
                                if ($address->mall && $address->mall->payment_method_id) {
                                    $set('payment_method_id', $address->mall->payment_method_id);
                                }
                            } else {
                                // No mall or mall_id is 0 - use customer's payment method
                                $customerId = $get('customer_id');
                                if ($customerId) {
                                    $customer = Customer::find($customerId);
                                    if ($customer && $customer->payment_method_id) {
                                        $set('payment_method_id', $customer->payment_method_id);
                                    }
                                }
                            }
                        }
                    } else {
                        // Clear driver data when no address is selected
                        $set('driver_id', null);
                        $set('driver_route', null);
                        $set('backup_driver_id', null);
                    }

                    // Call the specific handler if it exists
                    if (method_exists($this, 'handleAddressChanged')) {
                        $this->handleAddressChanged($state, $set, $get);
                    }
                })
        ]);
    }

    protected function getPaymentGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('payment_status_id')
                ->label('Payment Status')
                ->extraInputAttributes([
                    'data-id' => 'payment_status_id'
                ])
                ->placeholder('Select Payment Status')
                ->required()
                ->searchable()
                ->preload()
                ->options(OrderStatus::query()->pluck('label', 'id'))
                ->default($this->getDefaultPaymentStatusId())
                ->live(),

            Select::make('payment_method_id')
                ->label('Payment Method')
                ->extraInputAttributes([
                    'data-id' => 'payment_method_id'
                ])
                ->placeholder('Select Payment Method')
                ->required()
                ->searchable()
                ->preload()
                ->options(AttrPaymentMethod::query()->pluck('label', 'id'))
                ->live(),
        ]);
    }

    protected function getDriverGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('driver_id')
                ->label('Driver')
                ->extraInputAttributes([
                    'data-id' => 'driver_id'
                ])
                ->placeholder('Select Driver')
                ->required()
                ->searchable()
                ->preload()
                ->options(Driver::where('status_id', 1)->pluck('name', 'id'))
                ->getOptionLabelUsing(fn($value): ?string => Driver::find($value)?->name)
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('driver_route', null);
                }),

            Select::make('driver_route')
                ->label('Route')
                ->extraInputAttributes([
                    'data-id' => 'driver_route'
                ])
                ->placeholder('Select Route')
                ->required()
                ->searchable()
                ->options(function (callable $get) {
                    $driverId = $get('driver_id');
                    if (blank($driverId)) {
                        return [];
                    }

                    $driver = Driver::find($driverId);
                    if (!$driver || !$driver->route) {
                        return [];
                    }
                    return collect($driver->route)->pluck('route_name', 'route_name');
                })
                ->disabled(fn(callable $get): bool => blank($get('driver_id')))
        ]);
    }

    protected function getBackupDriverGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('backup_driver_id')
                ->label('Backup Driver')
                ->extraInputAttributes([
                    'data-id' => 'backup_driver_id'
                ])
                ->placeholder('Select Backup Driver')
                ->searchable()
                ->preload()
                ->options(Driver::where('status_id', 1)->pluck('name', 'id'))
                ->getOptionLabelUsing(fn($value): ?string => Driver::find($value)?->name)
        ]);
    }

    protected function getMealSelect(): Select
    {
        return Select::make('meal_id')
            ->label('Meal')
            ->extraInputAttributes([
                'data-id' => 'meal_id'
            ])
            ->placeholder('Select Meal')
            ->required()
            ->searchable()
            ->preload()
            ->options(function () {
                return Meal::query()
                    ->where('status_id', 1)
                    ->where('category_id', 1)
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->columnSpan(2);
    }

    private function calculateDeliveryFee($address, $totalQty)
    {
        if ($address && $address->mall_id) {
            return 0;
        }

        if (!$address || !$address->area_id) {
            return 0;
        }

        $area = Area::find($address->area_id);
        if (!$area || !$area->delivery_fee) {
            return 0;
        }

        $deliveryFeeRules = $area->delivery_fee;

        usort($deliveryFeeRules, function ($a, $b) {
            return $b['qty'] - $a['qty'];
        });

        foreach ($deliveryFeeRules as $rule) {
            if ($totalQty >= $rule['qty']) {
                return $rule['delivery_fee'];
            }
        }

        return end($deliveryFeeRules)['delivery_fee'] ?? 0;
    }

    // Common page functionality
    public array $data = [];
    public array $modalData = [];

    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'updatedData')) {
            $this->modalData = $this->getFormattedData();
            return;
        }

        return parent::__call($method, $parameters);
    }

    protected function getDriverSection(): Section
    {
        return Section::make('Driver Information')
            ->collapsible()
            ->schema([
                Grid::make(2)->schema([
                    DateTimePicker::make('arrival_time')
                        ->extraInputAttributes([
                            'data-id' => 'payment_status_id'
                        ])
                        ->withoutDate()
                        ->label('Arrival Time')
                        ->placeholder('Select Arrival Time')
                        ->required()
                        ->displayFormat('h:i A')
                        ->format('H:i')
                        ->withoutSeconds()
                ]),
                $this->getDriverGrid(),
                $this->getBackupDriverGrid(),
                Textarea::make('driver_notes')
                    ->extraInputAttributes([
                        'data-id' => 'driver_notes'
                    ])
                    ->label('Notes')
                    ->rows(5)
            ]);
    }

    protected function getMealsRepeater(string $orderType = 'single'): Repeater
    {
        return Repeater::make('meals')
            ->label('Meals')
            ->extraAttributes([
                'data-id' => 'meals'
            ])
            ->defaultItems(1)
            ->reorderable(false)
            ->deletable(true)
            ->cloneable()
            ->columns(6)
            //->live()
            ->addAction(
                fn($action) => $action
                    ->label('Add Meal')
                    ->extraAttributes(['class' => ''])
            )
            ->schema([
                $this->getMealSelect(),
                $this->createMealQuantityField('normal', 'Normal', $orderType),
                $this->createMealQuantityField('big', 'Big', $orderType),
                $this->createMealQuantityField('small', 'Small', $orderType),
                $this->createMealQuantityField('no_rice', 'No Rice', $orderType),
            ]);
    }

    protected function getTotalAmountField(): TextInput
    {
        return TextInput::make('total_amount')
            ->label('Total')
            ->extraInputAttributes([
                'data-id' => 'total_amount'
            ])
            ->placeholder('0.00')
            ->numeric()
            ->default(0.00)
            ->prefix('RM')
            ->rules(['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/']);
    }

    protected function getNotesField(): Textarea
    {
        return Textarea::make('notes')
            ->label('Notes')
            ->rows(5);
    }

    protected function getOrderInformationSection(): Section
    {
        return Section::make('Order Information')
            ->collapsible();
    }

    protected function getCommonValidationRules(): array
    {
        return [
            'data.customer_id' => ['required', 'exists:customers,id'],
            'data.address_id' => ['required', 'exists:customer_address_books,id'],
            'data.payment_status_id' => ['required', 'exists:order_statuses,id'],
            'data.payment_method_id' => ['required', 'exists:attr_payment_methods,id'],
            'data.arrival_time' => ['required'],
            'data.total_amount' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'data.notes' => ['nullable', 'string'],
            'data.driver_id' => ['required', 'exists:drivers,id'],
            'data.driver_route' => ['required', 'string'],
            'data.backup_driver_id' => ['nullable', 'exists:drivers,id'],
            'data.driver_notes' => ['nullable', 'string'],
        ];
    }

    protected function getFormattedAddressDisplay($address): string
    {
        if (!$address) {
            return '';
        }

        ob_start(); ?>
        <?php echo e($address->name); ?><br />
        <?php echo e($address->address_1); ?><br />
        <?php echo ($address->address_2) ? e($address->address_2) . '<br />' : ""; ?>
        <?php echo e($address->postcode); ?> <?php echo e($address->city); ?>
<?php
        return trim(ob_get_clean());
    }

    protected function calculateTotalQuantity(array $meals): int
    {
        $totalQty = 0;
        foreach ($meals as $meal) {
            $totalQty += intval($meal['normal']) + intval($meal['big']) +
                intval($meal['small']) +
                intval($meal['no_rice']);
        }
        return $totalQty;
    }

    protected function calculateTotalAmountByMealQty(callable $set, callable $get)
    {
        // Check if we're in CreateOrder context (has meals_by_date)
        $formData = $this->form->getRawState();

        if (isset($formData['meals_by_date'])) {
            // CreateOrder logic - handle meals_by_date structure
            $currentItem = $get('../../');  // Go up to the meals_by_date item level

            \Log::info('Debug calculateTotalAmountByMealQty', [
                'currentItem' => $currentItem,
                'has_date' => isset($currentItem['date']),
                'date_value' => $currentItem['date'] ?? 'NOT SET'
            ]);

            if (empty($currentItem['date']) || !isset($formData['meals_by_date'])) {
                \Log::info('Exiting early - no date or no meals_by_date');
                return;
            }

            // Find the current date item and calculate total for its meals
            foreach ($formData['meals_by_date'] as $index => $dateItem) {
                if (isset($dateItem['date']) && isset($currentItem['date']) && $dateItem['date'] === $currentItem['date']) {
                    $meals = $dateItem['meals'] ?? [];

                    // Calculate total quantity for all meals in this date
                    $totalMeals = 0;
                    foreach ($meals as $meal) {
                        $totalMeals += intval($meal['normal'] ?? 0) +
                            intval($meal['big'] ?? 0) +
                            intval($meal['small'] ?? 0) +
                            intval($meal['no_rice'] ?? 0);
                    }

                    // Get meal_price from config
                    $mealPrice = config('app.meal_price', 8.00);
                    $totalAmount = $totalMeals * $mealPrice;

                    // Update only the total_amount field for this date
                    $set('../../total_amount', number_format($totalAmount, 2));
                    break;
                }
            }
        } else {
            // EditOrder logic - handle direct meals structure
            $meals = $formData['meals'] ?? [];

            $totalMeals = 0;
            foreach ($meals as $meal) {
                $totalMeals += intval($meal['normal'] ?? 0) +
                    intval($meal['big'] ?? 0) +
                    intval($meal['small'] ?? 0) +
                    intval($meal['no_rice'] ?? 0);
            }

            // Get MEAL_PRICE from config
            $mealPrice = config('app.meal_price', 8.00);
            $totalAmount = $totalMeals * $mealPrice;

            // Update only the total_amount field
            $set('total_amount', number_format($totalAmount, 2));
        }
    }

    public function getBreadcrumbs(): array
    {
        $record = method_exists($this, 'getRecord') ? $this->getRecord() : null;
        $basePath = '/' . config('filament.path', 'backend') . '/orders';

        if ($record) {
            return [
                $basePath => 'Orders',
                '' => 'Order ' . $record->formatted_id,
            ];
        }

        return [
            $basePath => 'Orders',
            '' => 'New Order',
        ];
    }
}
