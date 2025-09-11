<?php

namespace App\Filament\Pages\Order;

use Illuminate\Support\HtmlString;

use App\Filament\Pages\AbstractFilamentPage;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Driver;
use App\Models\Meal;
use App\Models\Area;

class CreateOrder extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static ?string $navigationLabel = 'New Order';
    protected static ?string $title = 'New Order';
    protected static ?string $slug = 'orders/create';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.order.create-order';

    public array $data = [];
    public array $modalData = [];

    public function mount(): void
    {
        // Get first active meal for default
        $defaultMeal = Meal::where('status_id', 1)->first();
        $defaultMealId = $defaultMeal ? $defaultMeal->id : '';

        $this->form->fill([
            'customer_id' => '',
            'address_id' => '',
            'delivery_date' => '',
            'meals_by_date' => [],
            'total_amount' => 0.00,
            'notes' => '',
            'arrival_time' => '',
            'driver_id' => '',
            'driver_route' => '',
            'backup_driver_id' => '',
            'driver_notes' => '',
        ]);


        $devAutofill = TRUE;
        // For development: Get first active customer, their default address, and first active meal
        if ($devAutofill && app()->environment('local')) {
            $customer = Customer::where('status_id', 1)->first();
            $address = $customer ? CustomerAddressBook::where('customer_id', $customer->id)
                ->where('status_id', 1)
                ->where('is_default', true)
                ->first() : null;
            $driver = Driver::where('status_id', 1)->first();
            $driverRoute = $driver && $driver->route ? $driver->route[0]['route_name'] : null;

            // Get two different meals for variety
            $defaultMeal = Meal::where('status_id', 1)->first();
            $secondMeal = Meal::where('status_id', 1)->where('id', '!=', $defaultMeal->id)->first();
            // Get a random active meal using Laravel's built-in methods
            $randomMeal = Meal::where('status_id', 1)->inRandomOrder()->first();
            if (!$secondMeal) $secondMeal = $defaultMeal; // Fallback to same meal if no other exists

            $orderData = [
                'customer_id' => $customer ? $customer->id : '',
                'address_id' => $address ? $address->id : '',
                'arrival_time' => '08:00',
                'driver_id' => $driver ? $driver->id : '',
                'driver_route' => $driverRoute,
                'backup_driver_id' => '',
                'driver_notes' => 'Sample driver notes',
            ];

            $days = 1;

            $startDate = \Carbon\Carbon::today();

            // Generate individual dates with comma separator
            $dates = [];
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $startDate->copy()->addDays($i)->format(config('app.date_format'));
            }
            $orderData['delivery_date'] = $dates;

            // Generate meals_by_date dynamically based on $days
            $mealsByDate = [];
            for ($i = 0; $i < $days; $i++) {
                $currentDate = $startDate->copy()->addDays($i);

                // Vary the meal quantities and amounts for different days
                $dayNumber = $i + 1;
                $isEvenDay = ($dayNumber % 2 == 0);

                $tempMeals = [
                    'date' => $currentDate->format(config('app.date_format')),
                    'meals' => [
                        [
                            'meal_id' => $randomMeal->id,
                            'normal' => 1,
                            'big' => 0,
                            'small' => 0,
                            's_small' => 0,
                            'no_rice' => 1
                        ],
                    ],
                    'total_amount' => $isEvenDay ? 120.00 : 100.00,
                    'notes' => "Sample order notes for day {$dayNumber}"
                ];

                $meals_no = rand(1, 3);
                for($j = 0; $j <= $meals_no; $j++){
                    array_push($tempMeals['meals'], [
                        'meal_id' => $randomMeal->id,
                        'normal' => rand(0, 3),
                        'big' => rand(0, 3),
                        'small' => rand(0, 3),
                        's_small' => rand(0, 3),
                        'no_rice' => rand(0, 3)
                    ]);
                }
                $mealsByDate[] = $tempMeals;
            }

            $orderData['meals_by_date'] = $mealsByDate;
            $this->form->fill($orderData);
        }

        // Initialize modal data
        $this->modalData = $this->getFormattedData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Order Information')
                ->collapsible()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_id')
                                ->label('Customer Name')
                                ->placeholder('Select Customer')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(Customer::query()->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('address_id', null);
                                }),

                            Select::make('address_id')
                                ->label('Delivery Location')
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
                <div class="hidden"><?php echo "{$address->name}|{$address->city}"; ?></div>
                <span class="font-bold"><?php echo $address->name; ?></span><?php echo $address->is_default ? '<span class="italic text-xs text-gray-400"> (default)</span>' : ""; ?>
                <div><?php echo $address->address_1; ?><br />
                    <?php echo ($address->address_2) ? $address->address_2 . '<br />' : ""; ?>
                    <?php echo $address->postcode; ?> <?php echo $address->city; ?></div>
            <?php
                                            $displayAddress = trim(ob_get_clean());
                                            return [$address->id => $displayAddress];
                                        });
                                })
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // Load driver information based on selected address
                                    if ($state) {
                                        $address = CustomerAddressBook::find($state);
                                        if ($address) {
                                            // Use pre-assigned driver information from the address only if current fields are empty
                                            if ($address->driver_id && !$get('driver_id')) {
                                                $set('driver_id', $address->driver_id);
                                                if ($address->driver_route && !$get('driver_route')) {
                                                    $set('driver_route', $address->driver_route);
                                                }
                                            }

                                            // Use pre-assigned backup driver information from the address only if current fields are empty
                                            if ($address->backup_driver_id && !$get('backup_driver_id')) {
                                                $set('backup_driver_id', $address->backup_driver_id);
                                            }
                                        }
                                    }
                                })
                        ]),
                    //DateRangePicker::make('delivery_date_range')
                    //    ->label('Delivery Date')
                    //    ->required()
                    //    ->minDate(\Carbon\Carbon::now())
                    //    ->live()
                    //    ->afterStateUpdated(function ($state, callable $set) {
                    //        if (empty($state)) {
                    //            $set('meals_by_date', []);
                    //            return;
                    //        }

                    //        [$startDate, $endDate] = explode(' - ', $state);
                    //        $startDate = \Carbon\Carbon::parse(str_replace('/', '-', $startDate));
                    //        $endDate = \Carbon\Carbon::parse(str_replace('/', '-', $endDate));

                    //        $meals_by_date = [];
                    //        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    //            $meals_by_date[] = [
                    //                'date' => $date->format(config('app.date_format')),
                    //                'meals' => [
                    //                    [
                    //                        'meal_id' => '',
                    //                        'normal' => 0,
                    //                        'big' => 0,
                    //                        'small' => 0,
                    //                        's_small' => 0,
                    //                        'no_rice' => 0,
                    //                    ]
                    //                ],
                    //                'total_amount' => 0.00,
                    //                'notes' => ''
                    //            ];
                    //        }

                    //        $set('meals_by_date', $meals_by_date);
                    //    })
                    Flatpickr::make('delivery_date')
                        ->label('Delivery Dates')
                        ->multiplePicker() // This enables multiple date selection
                        ->format(config('app.date_format'))
                        ->displayFormat(config('app.date_format'))
                        ->conjunction(', ') // Set separator between multiple dates
                        ->minDate(fn() => today())
                        ->required()
                        ->live()
                        ->debounce(0) // Wait 500ms after last change
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (empty($state)) {
                                $set('meals_by_date', []);
                                return;
                            }

                            $dates = explode(',', $state);

                            $meals_by_date = [];
                            foreach ($dates as $dateString) {
                                $date = \Carbon\Carbon::parse(trim($dateString));
                                $meals_by_date[] = [
                                    'date' => $date->format(config('app.date_format')),
                                    'meals' => [
                                        [
                                            'meal_id' => '',
                                            'normal' => 0,
                                            'big' => 0,
                                            'small' => 0,
                                            's_small' => 0,
                                            'no_rice' => 0,
                                        ]
                                    ],
                                    'total_amount' => 0.00,
                                    'notes' => ''
                                ];
                            }

                            $set('meals_by_date', $meals_by_date);
                        })
                ]),

            Repeater::make('meals_by_date')
                ->label('')
                ->reorderable(false)
                ->deletable(false)
                ->disableItemCreation()
                ->schema([
                    Placeholder::make('date_label')
                        ->label(function (callable $get) {
                            $currentItem = $get('.');  // Gets the current repeater item's data
                            return sprintf(
                                "Order - %s",
                                \Carbon\Carbon::parse($currentItem['date'])->format(config('app.date_format'))
                            );
                        }),
                    Repeater::make('meals')
                        ->label('Meals')
                        ->defaultItems(1)
                        ->reorderable(false)
                        ->deletable(true)
                        ->cloneable()
                        ->columns(7)
                        ->addAction(
                            fn($action) => $action
                                ->label('Add Meal')
                                ->extraAttributes([
                                    'class' => '',
                                ])
                        )
                        ->schema([
                            Select::make('meal_id')
                                ->label('Meal')
                                ->placeholder('Select Meal')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(Meal::query()->where('status_id', 1)->pluck('name', 'id'))
                                ->live()
                                ->columnSpan(2),
                            TextInput::make('normal')
                                ->label('Normal')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->required(),
                            TextInput::make('big')
                                ->label('Big')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->required(),
                            TextInput::make('small')
                                ->label('Small')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->required(),
                            TextInput::make('s_small')
                                ->label('S.Small')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->required(),
                            TextInput::make('no_rice')
                                ->label('No Rice')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->required(),
                        ]),

                    TextInput::make('total_amount')
                        ->label('Total')
                        ->placeholder('0.00')
                        ->numeric()
                        ->default(0.00)
                        ->prefix('RM')
                        ->rules(['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'])
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $state = (int)ltrim($state, '0') ?: 0;
                            $set('total_amount', $state);
                        }),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(5)
                        ->live()
                ])
                ->columns(1),
            Section::make('Driver Information')
                ->collapsible()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('arrival_time')
                                ->withoutDate()
                                ->label('Arrival Time')
                                ->placeholder('Select Arrival Time')
                                ->required()
                                //->timezone('Asia/Kuala_Lumpur')
                                ->withoutDate()
                                ->displayFormat('h:i A') // 12-hour with AM/PM (K = AM/PM)
                                ->format('H:i')
                                ->withoutSeconds()
                                ->live()
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('driver_id')
                                ->label('Driver')
                                ->placeholder('Select Driver')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(Driver::query()->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('driver_route', null);
                                }),
                            Select::make('driver_route')
                                ->label('Route')
                                ->placeholder('Select Route')
                                ->required()
                                ->searchable()
                                ->live()
                                ->options(function (callable $get) {
                                    $driverId = $get('driver_id');

                                    if (blank($driverId)) {
                                        return [];
                                    }

                                    $driver = \App\Models\Driver::find($driverId);
                                    if (!$driver || !$driver->route) {
                                        return [];
                                    }
                                    return collect($driver->route)->pluck('route_name', 'route_name');
                                })
                                ->disabled(fn(callable $get): bool => blank($get('driver_id')))
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('backup_driver_id')
                                ->label('Backup Driver')
                                ->placeholder('Select Backup Driver')
                                ->searchable()
                                ->preload()
                                ->options(Driver::query()->pluck('name', 'id'))
                                ->live()
                        ]),
                    Textarea::make('driver_notes')
                        ->label('Notes')
                        ->rows(5)
                        ->live()
                ]),
        ];
    }

    public function create()
    {
        // Validate the form data
        $data = $this->form->getState();

        $this->validate([
            'data.customer_id' => ['required', 'exists:customers,id'],
            'data.address_id' => ['required', 'exists:customer_address_books,id'],
            'data.delivery_date' => ['required', 'string'],
            'data.arrival_time' => ['required'],
            'data.meals_by_date' => ['required', 'array', 'min:1'],
            'data.driver_id' => ['required', 'exists:drivers,id'],
            'data.driver_route' => ['required', 'string'],
            'data.backup_driver_id' => ['nullable', 'exists:drivers,id'],
            'data.driver_notes' => ['nullable', 'string'],
        ]);

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Get customer address for delivery fee calculation
            $address = CustomerAddressBook::find($data['address_id']);

            // Create an order for each date
            foreach ($data['meals_by_date'] as $date => $dateData) {
                // Skip if no meals for this date
                if (empty($dateData['meals'])) {
                    continue;
                }

                // Calculate total quantity for this date
                $totalQty = 0;
                foreach ($dateData['meals'] as $meal) {
                    $totalQty += intval($meal['normal']) + intval($meal['big']) + intval($meal['small']) + intval($meal['s_small']) + intval($meal['no_rice']);
                }

                // Calculate delivery fee for this date
                $deliveryFee = $this->calculateDeliveryFee($address, $totalQty);

                // Get customer's payment method
                $customer = Customer::find($data['customer_id']);
                $paymentMethodId = $customer ? $customer->payment_method_id : null;

                $order = \App\Models\Order::create([
                    'customer_id' => $data['customer_id'],
                    'address_id' => $data['address_id'],
                    'delivery_date' => $dateData['date'],
                    'total_amount' => $dateData['total_amount'],
                    'delivery_fee' => $deliveryFee,
                    'notes' => $dateData['notes'] ?? '',
                    'arrival_time' => $data['arrival_time'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'] ?? 0,
                    'driver_notes' => $data['driver_notes'] ?? '',
                    'payment_method_id' => $paymentMethodId,
                ]);

                // Create invoice for this order
                $billingAddress = $address->address_1 . "\n" .
                    ($address->address_2 ? $address->address_2 . "\n" : '') .
                    $address->mall_or_area;

                $invoice = \App\Models\Invoice::create([
                    'order_id' => $order->id,
                    'invoice_no' => $order->invoice_no,
                    'billing_name' => $customer->name,
                    'billing_address' => $billingAddress,
                    'tax_amount' => config('app.tax_rate'),
                    'issue_date' => now(),
                    'due_date' => now()->addDays(30),
                ]);

                // Create order meals for this date
                foreach ($dateData['meals'] as $meal) {
                    \App\Models\OrderMeal::create([
                        'order_id' => $order->id,
                        'meal_id' => $meal['meal_id'],
                        'normal' => $meal['normal'],
                        'big' => $meal['big'],
                        'small' => $meal['small'],
                        's_small' => $meal['s_small'],
                        'no_rice' => $meal['no_rice'],
                    ]);
                }
            }

            \DB::commit();

            Notification::make()
                ->success()
                ->title('Orders created successfully')
                ->send();

            $this->redirect('/' . config('filament.path', 'backend') . '/orders');
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error creating orders')
                ->body($e->getMessage())
                ->send();
        }
    }

    private function calculateTotalAmount($meals)
    {
        $total = 0.00;
        foreach ($meals as $meal) {
            $mealData = \App\Models\Meal::find($meal['meal_id']);
            if ($mealData) {
                $total += $mealData->price * (
                    $meal['normal'] +
                    $meal['big'] +
                    $meal['small'] +
                    $meal['s_small'] +
                    $meal['no_rice']
                );
            }
        }
        return $total;
    }

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);

        $meals_by_date = [];

        // Handle meals_by_date structure (for Create Order)
        if (isset($this->data['meals_by_date']) && is_array($this->data['meals_by_date'])) {
            foreach ($this->data['meals_by_date'] as $dateData) {
                if (isset($dateData['date']) && isset($dateData['meals'])) {
                    $meal_ids = collect($dateData['meals'])->pluck('meal_id')->toArray();
                    $meals = Meal::whereIn('id', $meal_ids)->get()->keyBy('id');

                    $formatted_meals = [];
                    $total_qty = 0;
                    foreach ($dateData['meals'] as $meal) {
                        if (isset($meal['meal_id']) && isset($meals[$meal['meal_id']])) {
                            $meal_qty = intval($meal['normal']) + intval($meal['big']) + intval($meal['s_small']) + intval($meal['small']) + intval($meal['no_rice']);
                            $total_qty += $meal_qty;
                            $formatted_meals[] = [
                                'meal_id' => $meal['meal_id'],
                                'name' => $meals[$meal['meal_id']]->name,
                                'normal' => $meal['normal'],
                                'big' => $meal['big'],
                                'small' => $meal['small'],
                                's_small' => $meal['s_small'],
                                'no_rice' => $meal['no_rice'],
                                'qty' => $meal_qty,
                            ];
                        }
                    }

                    // Calculate delivery fee based on address area and total quantity
                    $delivery_fee = $this->calculateDeliveryFee($address, $total_qty);

                    $meals_by_date[$dateData['date']] = [
                        'meals' => $formatted_meals,
                        'total_amount' => $dateData['total_amount'],
                        'delivery_fee' => $delivery_fee,
                        'notes' => $dateData['notes'] ?? ''
                    ];
                }
            }
        }

        $display_address = '';
        if ($address) {
            ob_start(); ?>
            <?php echo $address->name; ?><br />
            <?php echo $address->address_1; ?><br />
            <?php echo ($address->address_2) ? $address->address_2 . '<br />' : ""; ?>
            <?php echo $address->postcode; ?> <?php echo $address->city; ?>
<?php
            $display_address = trim(ob_get_clean());
        }

        $delivery_date = '';

        // Parse the date range
        if ($this->data['delivery_date'] !== '') {
            // Parse comma-separated dates
            $dateStrings = explode(', ', $this->data['delivery_date']);

            foreach ($dateStrings as $dateString) {
                $date = \Carbon\Carbon::parse(str_replace('/', '-', $dateString));
                $delivery_date .= ($delivery_date != '' ? ', ' : '') . $date->format(config('app.date_format'));
            }
        }

        return [
            'customer_id' => $this->data['customer_id'],
            'customer_name' => $customer ? $customer->name : '',
            'address_id' => $this->data['address_id'],
            'address' => $address ? $display_address : '',
            'delivery_date' => $delivery_date,
            'meals_by_date' => $meals_by_date,
            'total_amount' => array_sum(array_column($meals_by_date, 'total_amount')),
            'total_delivery_fee' => array_sum(array_column($meals_by_date, 'delivery_fee')),
            'arrival_time' => isset($this->data['arrival_time']) && !empty($this->data['arrival_time'])
                ? date('h:i A', strtotime($this->data['arrival_time']))
                : '',
            'driver_id' => $this->data['driver_id'] ?? '',
            'driver_name' => Driver::find($this->data['driver_id'] ?? null) ? Driver::find($this->data['driver_id'] ?? null)->name : '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => Driver::find($this->data['backup_driver_id'] ?? null) ? Driver::find($this->data['backup_driver_id'] ?? null)->name : '',
            'driver_notes' => $this->data['driver_notes'] ?? '',
        ];
    }

    public function updatedDataCustomerId()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataAddressId()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDeliveryDateRange()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataMealsByDate()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataArrivalTime()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverId()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverRoute()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataBackupDriverId()
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverNotes()
    {
        $this->modalData = $this->getFormattedData();
    }

    private function calculateDeliveryFee($address, $totalQty)
    {
        if (!$address || !$address->area_id) {
            return 0;
        }

        $area = Area::find($address->area_id);
        if (!$area || !$area->delivery_fee) {
            return 0;
        }

        $deliveryFeeRules = $area->delivery_fee;

        // Sort by qty in descending order to find the highest applicable tier
        usort($deliveryFeeRules, function ($a, $b) {
            return $b['qty'] - $a['qty'];
        });

        // Find the appropriate delivery fee based on total quantity
        foreach ($deliveryFeeRules as $rule) {
            if ($totalQty >= $rule['qty']) {
                return $rule['delivery_fee'];
            }
        }

        // If no rule matches, return the fee for the lowest quantity tier
        return end($deliveryFeeRules)['delivery_fee'] ?? 0;
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/' . config('filament.path', 'backend') . '/orders' => 'Orders',
            '' => 'New Order',
        ];
    }
}
