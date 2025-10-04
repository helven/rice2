<?php

namespace App\Filament\Pages\Order;

use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Driver;
use App\Models\Meal;
use App\Models\Area;
use App\Models\AttrPaymentMethod;
use App\Models\OrderStatus;
use App\Traits\OrderFormTrait;

class CreateOrder extends Page
{
    use InteractsWithForms, OrderFormTrait;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static ?string $navigationLabel = 'New Order';
    protected static ?string $title = 'New Order';
    protected static ?string $slug = 'orders/create';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.order.create-order';
    
    // Dev toggle - set to true to enable autofill in local environment
    private bool $devAutofill = false;

    public function mount(): void
    {
        $this->form->fill([
            'customer_id' => '',
            'address_id' => '',
            'payment_status_id' => $this->getDefaultPaymentStatusId(),
            'payment_method_id' => '',
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

        $this->fillDevData();

        // Initialize modal data
        $this->modalData = $this->getFormattedData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }



    // CreateOrder specific handlers
    public function onCustomerChanged($state, callable $set, callable $get)
    {
        // CreateOrder specific JavaScript
        $this->js('
            setTimeout(() => {
                const customerId = ' . json_encode($state) . ';
                const addressId = null;
                if (typeof fetchExistingDeliveryDates === "function") {
                    fetchExistingDeliveryDates(customerId, addressId);
                }
            }, 100);
        ');
    }

    public function onAddressChanged($state, callable $set, callable $get)
    {
        // CreateOrder specific JavaScript
        $customerId = $get('customer_id');
        $this->js('
            setTimeout(() => {
                const customerId = ' . json_encode($customerId) . ';
                const addressId = ' . json_encode($state) . ';
                if (typeof fetchExistingDeliveryDates === "function") {
                    fetchExistingDeliveryDates(customerId, addressId);
                }
            }, 100);
        ');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Order Information')
                ->collapsible()
                ->schema([
                    $this->getCustomerAddressGrid(),
                    $this->getPaymentGrid(),
                    
                    Flatpickr::make('delivery_date')
                        ->id('delivery_date')
                        ->label('Delivery Dates')
                        ->multiplePicker()
                        ->format(config('app.date_format'))
                        ->displayFormat(config('app.date_format'))
                        ->conjunction(', ')
                        ->minDate(fn() => today())
                        ->required()
                        ->live()
                        ->debounce(0)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty($state)) {
                                $set('meals_by_date', []);
                                return;
                            }

                            // Handle both string and array formats
                            $dates = is_array($state) ? $state : explode(',', $state);
                            $existingMealsByDate = $get('meals_by_date') ?? [];

                            // Create a lookup array for existing dates
                            $existingDateLookup = [];
                            foreach ($existingMealsByDate as $existingItem) {
                                $existingDateLookup[$existingItem['date']] = $existingItem;
                            }

                            $meals_by_date = [];
                            foreach ($dates as $dateString) {
                                $date = \Carbon\Carbon::parse(trim($dateString));
                                $formattedDate = $date->format(config('app.date_format'));

                                // Check if this date already exists in the form data
                                if (isset($existingDateLookup[$formattedDate])) {
                                    // Retain existing data for this date
                                    $meals_by_date[] = $existingDateLookup[$formattedDate];
                                } else {
                                    // Create new entry for new date
                                    $meals_by_date[] = [
                                        'date' => $formattedDate,
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
                            }

                            $set('meals_by_date', $meals_by_date);
                        })
                ]),

            Repeater::make('meals_by_date')
                ->label('')
                ->reorderable(false)
                ->deletable(true)
                ->disableItemCreation()
                ->collapsible()
                ->itemLabel(function (array $state) {
                    if (isset($state['date'])) {
                        return new HtmlString(sprintf(
                            '<h3 class="fi-section-header-heading text-sm font-semibold leading-6 text-gray-950 dark:text-white">Order - %s</h3>',
                            \Carbon\Carbon::parse($state['date'])->format(config('app.date_format'))
                        ));
                    }
                    return null;
                })
                ->live()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    // Extract dates from remaining meals_by_date items
                    $dates = [];
                    if (is_array($state)) {
                        foreach ($state as $item) {
                            if (isset($item['date']) && !empty($item['date'])) {
                                $dates[] = $item['date'];
                            }
                        }
                    }

                    // Update both fields
                    $set('delivery_date', implode(', ', $dates));

                    // Force Flatpickr to update using direct JavaScript
                    $this->js('
                        setTimeout(() => {
                            let input = document.querySelector("#delivery_date.flatpickr-input");
                            
                            if (input && input._flatpickr) {
                                input._flatpickr.clear();
                                input._flatpickr.setDate("' . implode(', ', $dates) . '", true);
                            }
                        }, 10);
                    ');
                })
                ->schema([
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
                                ->extraAttributes(['class' => ''])
                        )
                        ->schema([
                            $this->getMealSelect(),
                            $this->createMealQuantityField('normal', 'Normal'),
                            $this->createMealQuantityField('big', 'Big'),
                            $this->createMealQuantityField('small', 'Small'),
                            $this->createMealQuantityField('s_small', 'S.Small'),
                            $this->createMealQuantityField('no_rice', 'No Rice'),
                        ]),

                    $this->getTotalAmountField(),
                    $this->getNotesField()
                ])
                ->columns(1),
                
            $this->getDriverSection(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Save')
                ->action('create')
                ->keyBindings(['mod+s'])
                ->color('primary'),
            Action::make('createAnother')
                ->label('Save & Create another')
                ->action('createAnother')
                ->keyBindings(['mod+shift+s'])
                ->color('gray'),
            Action::make('cancel')
                ->label('Cancel')
                ->url('/' . config('filament.path', 'backend') . '/orders')
                ->color('gray'),
        ];
    }

    public function create()
    {
        $this->createOrder(false);
    }

    public function createAnother()
    {
        $this->createOrder(true);
    }

    protected function createOrder(bool $createAnother = false)
    {
        // Validate the form data
        $data = $this->form->getState();

        $this->validate(array_merge($this->getCommonValidationRules(), [
            'data.delivery_date' => ['required', 'string'],
            'data.meals_by_date' => ['required', 'array', 'min:1'],
        ]));

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
                $totalQty = $this->calculateTotalQuantity($dateData['meals']);

                // Calculate delivery fee for this date
                $deliveryFee = $this->calculateDeliveryFee($address, $totalQty);

                // Create order with only order-related fields
                $order = \App\Models\Order::create([
                    'customer_id' => $data['customer_id'],
                    'address_id' => $data['address_id'],
                    'payment_status_id' => $data['payment_status_id'],
                    'payment_method_id' => $data['payment_method_id'],
                    'delivery_date' => $dateData['date'],
                    'total_amount' => $dateData['total_amount'],
                    'delivery_fee' => $deliveryFee,
                    'notes' => $dateData['notes'] ?? '',
                ]);

                // Generate and set order number
                $orderNo = \App\Models\Order::generateOrderNumber(
                    $order->id,
                    $address->mall_id ?? null,
                    $dateData['date']
                );
                $order->update(['order_no' => $orderNo]);

                // Create delivery record with driver-related fields
                $deliveryService = app(\App\Services\DeliveryService::class);
                $deliveryData = [
                    'arrival_time' => $data['arrival_time'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'] ?? null,
                    'driver_notes' => $data['driver_notes'] ?? '',
                ];
                $deliveryService->storeDeliveryData($order, $deliveryData);

                // Create invoice for this order
                $this->createInvoice($order, $address);

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

            if ($createAnother) {
                // Reset the form for creating another order
                $this->form->fill([
                    'customer_id' => '',
                    'address_id' => '',
                    'payment_status_id' => $this->getDefaultPaymentStatusId(),
                    'payment_method_id' => '',
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
                $this->modalData = [];
            } else {
                $this->redirect('/' . config('filament.path', 'backend') . '/orders');
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error creating orders')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);

        $meals_by_date = [];

        // Handle meals_by_date structure (for Create Order)
        if (isset($this->data['meals_by_date']) && is_array($this->data['meals_by_date'])) {
            // Collect all meal IDs first
            $allMealIds = [];
            foreach ($this->data['meals_by_date'] as $dateData) {
                if (isset($dateData['meals'])) {
                    $mealIds = collect($dateData['meals'])->pluck('meal_id')->filter()->toArray();
                    $allMealIds = array_merge($allMealIds, $mealIds);
                }
            }
            
            // Single query for all meals
            $allMeals = Meal::whereIn('id', array_unique($allMealIds))->get()->keyBy('id');
            
            // Process each date using cached meals
            foreach ($this->data['meals_by_date'] as $dateData) {
                if (isset($dateData['date']) && isset($dateData['meals'])) {
                    $formatted_meals = [];
                    $total_qty = 0;
                    
                    foreach ($dateData['meals'] as $meal) {
                        if (isset($meal['meal_id']) && isset($allMeals[$meal['meal_id']])) {
                            $meal_qty = intval($meal['normal']) + intval($meal['big']) + 
                                       intval($meal['s_small']) + intval($meal['small']) + 
                                       intval($meal['no_rice']);
                            $total_qty += $meal_qty;
                            
                            $formatted_meals[] = [
                                'meal_id' => $meal['meal_id'],
                                'name' => $allMeals[$meal['meal_id']]->name,
                                'normal' => $meal['normal'],
                                'big' => $meal['big'],
                                'small' => $meal['small'],
                                's_small' => $meal['s_small'],
                                'no_rice' => $meal['no_rice'],
                                'qty' => $meal_qty,
                            ];
                        }
                    }
                    
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

        $display_address = $this->getFormattedAddressDisplay($address);

        $delivery_date = '';

        // Parse the date range
        if ($this->data['delivery_date'] !== '') {
            // Handle both string and array formats
            $dateStrings = is_array($this->data['delivery_date']) ? $this->data['delivery_date'] : explode(',', $this->data['delivery_date']);

            foreach ($dateStrings as $dateString) {
                $date = \Carbon\Carbon::parse(str_replace('/', '-', $dateString));
                $delivery_date .= ($delivery_date != '' ? ', ' : '') . $date->format(config('app.date_format'));
            }
        }

        $driver = Driver::find($this->data['driver_id'] ?? null);
        $backupDriver = Driver::find($this->data['backup_driver_id'] ?? null);

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
            'driver_name' => $driver?->name ?? '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => $backupDriver?->name ?? '',
            'driver_notes' => $this->data['driver_notes'] ?? '',
        ];
    }







    private function fillDevData(): void
    {
        if (!$this->devAutofill || !app()->environment('local')) {
            return;
        }

        $customer = Customer::where('status_id', 1)->first();
        $address = $customer ? CustomerAddressBook::where('customer_id', $customer->id)
            ->where('status_id', 1)
            ->where('is_default', true)
            ->first() : null;
        $driver = Driver::where('status_id', 1)->first();
        $driverRoute = $driver && $driver->route && !empty($driver->route) 
            ? $driver->route[0]['route_name'] 
            : null;
        $randomMeal = Meal::where('status_id', 1)->inRandomOrder()->first();

        $orderData = [
            'customer_id' => $customer ? $customer->id : '',
            'address_id' => $address ? $address->id : '',
            'payment_status_id' => $this->getDefaultPaymentStatusId(),
            'payment_method_id' => $customer ? $customer->payment_method_id : '',
            'arrival_time' => '08:00',
            'driver_id' => $driver ? $driver->id : '',
            'driver_route' => $driverRoute,
            'backup_driver_id' => '',
            'driver_notes' => 'Sample driver notes',
        ];

        $days = 3;
        $startDate = \Carbon\Carbon::today();
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->format(config('app.date_format'));
        }
        $orderData['delivery_date'] = $dates;

        $mealsByDate = [];
        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dayNumber = $i + 1;

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
                'total_amount' => 0.00,
                'notes' => "Sample order notes for day {$dayNumber}"
            ];

            $totalMeals = 0;
            foreach ($tempMeals['meals'] as $meal) {
                $totalMeals += intval($meal['normal'] ?? 0) +
                    intval($meal['big'] ?? 0) +
                    intval($meal['small'] ?? 0) +
                    intval($meal['s_small'] ?? 0) +
                    intval($meal['no_rice'] ?? 0);
            }
            $mealPrice = config('app.meal_price', 8.00);
            $tempMeals['total_amount'] = number_format($totalMeals * $mealPrice, 2);

            $mealsByDate[] = $tempMeals;
        }

        $orderData['meals_by_date'] = $mealsByDate;
        $this->form->fill($orderData);
    }
}