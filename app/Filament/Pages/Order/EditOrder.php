<?php

namespace App\Filament\Pages\Order;

use Illuminate\Support\HtmlString;

use App\Models\CustomerAddressBook;
use App\Filament\Pages\AbstractFilamentPage;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
use Filament\Forms\Components\Toggle;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Area;
use App\Models\AttrPaymentMethod;
use App\Models\AttrStatus;

class EditOrder extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?string $title = 'Edit Order';
    protected static ?string $slug = 'orders/{id}/edit';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.order.edit-order';

    public array $data = [];
    public ?Order $order = null;
    public array $modalData = [];

    public function mount($id): void
    {
        $this->order = Order::with(['meals', 'customer', 'address', 'driver', 'backup_driver'])->findOrFail($id);

        $this->form->fill([
            'id' => $this->order->formatted_id,
            'customer_id' => $this->order->customer_id,
            'address_id' => $this->order->address_id,
            'delivery_date' => $this->order->delivery_date,
            'payment_status_id' => $this->order->payment_status_id,
            'payment_method_id' => $this->order->payment_method_id,
            'meals' => $this->order->meals->map(function ($meal) {
                return [
                    'meal_id' => $meal->meal_id,
                    'normal' => $meal->normal,
                    'big' => $meal->big,
                    'small' => $meal->small,
                    's_small' => $meal->s_small,
                    'no_rice' => $meal->no_rice,
                ];
            })->toArray(),
            'total_amount' => $this->order->total_amount,
            'notes' => $this->order->notes,
            'arrival_time' => $this->order->arrival_time,
            'driver_id' => $this->order->driver_id,
            'driver_route' => $this->order->driver_route,
            'backup_driver_id' => ($this->order->backup_driver_id !== 0) ? $this->order->backup_driver_id : '',
            'driver_notes' => $this->order->driver_notes,
        ]);
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
                    TextInput::make('id')
                        ->label('Order No')
                        ->readonly(),
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
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('address_id', null);
                                    
                                    // Trigger JavaScript to update disabled dates for Edit Order
                                    $orderId = $get('id'); // Get current order ID
                                    $this->js('
                                        setTimeout(() => {
                                            const customerId = "' . $state . '";
                                            const addressId = null; // Address is reset when customer changes
                                            const orderId = "' . $orderId . '";
                                            if (typeof fetchExistingDeliveryDates === "function") {
                                                fetchExistingDeliveryDates(customerId, addressId, orderId);
                                            }
                                        }, 100);
                                    ');
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
                                            if ($address->driver_id) {
                                                $set('driver_id', $address->driver_id);
                                                if ($address->driver_route) {
                                                    $set('driver_route', $address->driver_route);
                                                }
                                            }

                                            // Use pre-assigned backup driver information from the address only if current fields are empty
                                            if ($address->backup_driver_id) {
                                                $set('backup_driver_id', $address->backup_driver_id);
                                            }
                                        }
                                    }
                                    
                                    // Trigger JavaScript to update disabled dates for Edit Order
                                    $customerId = $get('customer_id');
                                    $orderId = $get('id'); // Get current order ID
                                    $this->js('
                                        setTimeout(() => {
                                            const customerId = "' . $customerId . '";
                                            const addressId = "' . $state . '";
                                            const orderId = "' . $orderId . '";
                                            if (typeof fetchExistingDeliveryDates === "function") {
                                                fetchExistingDeliveryDates(customerId, addressId, orderId);
                                            }
                                        }, 100);
                                    ');
                                })
                        ]),

                    // Payment Information
                    Grid::make(2)
                        ->schema([
                            Select::make('payment_status_id')
                                ->label('Payment Status')
                                ->placeholder('Select Payment Status')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(OrderStatus::query()->pluck('label', 'id'))
                                ->live(),

                            Select::make('payment_method_id')
                                ->label('Payment Method')
                                ->placeholder('Select Payment Method')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(AttrPaymentMethod::query()->pluck('label', 'id'))
                                ->live(),
                        ]),

                    //DateTimePicker::make('delivery_date')
                    //    ->label('Delivery Date')
                    //    ->required()
                    //    //->minDate(\Carbon\Carbon::now())
                    //    ->timezone('Asia/Kuala_Lumpur')
                    //    ->displayFormat('Y/m/d') // 12-hour with AM/PM (K = AM/PM)
                    //    ->live()
                    Flatpickr::make('delivery_date')
                        ->id('delivery_date')
                        ->label('Delivery Date')
                        ->format(config('app.date_format'))
                        ->displayFormat(config('app.date_format'))
                        ->required()
                        ->live()
                ]),
            Section::make('Add Order')
                ->collapsible()
                ->schema([
                    // Repeater for Meal items
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
                                ->options(function () {
                                    return Meal::query()
                                        ->where('status_id', 1)
                                        ->where('category_id', 1)
                                        ->whereNotNull('name')
                                        ->where('name', '!=', '')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->live()
                                ->columnSpan(2),
                            TextInput::make('normal')
                                ->label('Normal')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('normal', $state);
                                    $this->calculateTotalAmountByMealQty($get, $set);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
                            TextInput::make('big')
                                ->label('Big')
                                ->numeric()
                                ->default(0)
                                ->extraInputAttributes(['min' => 0, 'max' => 1000])
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('big', $state);
                                    $this->calculateTotalAmountByMealQty($get, $set);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
                            TextInput::make('small')
                                ->label('Small')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('small', $state);
                                    $this->calculateTotalAmountByMealQty($get, $set);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
                            TextInput::make('s_small')
                                ->label('S.Small')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('s_small', $state);
                                    $this->calculateTotalAmountByMealQty($get, $set);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
                            TextInput::make('no_rice')
                                ->label('No Rice')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('no_rice', $state);
                                    $this->calculateTotalAmountByMealQty($get, $set);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
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
                ]),
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

    public function save()
    {
        // Validate the form data
        $data = $this->form->getState();

        $this->validate([
            'data.customer_id' => ['required', 'exists:customers,id'],
            'data.address_id' => ['required', 'exists:customer_address_books,id'],
            'data.delivery_date' => ['required', 'string'],
            'data.payment_status_id' => ['required', 'exists:order_statuses,id'],
            'data.payment_method_id' => ['required', 'exists:attr_payment_methods,id'],
            'data.arrival_time' => ['required'],
            'data.meals' => ['required', 'array', 'min:1'],
            'data.meals.*.meal_id' => ['required', 'exists:meals,id'],
            'data.meals.*.normal' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.big' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.small' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.s_small' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.no_rice' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.total_amount' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'data.notes' => ['nullable', 'string'],
            'data.driver_id' => ['required', 'exists:drivers,id'],
            'data.driver_route' => ['required', 'string'],
            'data.backup_driver_id' => ['nullable', 'exists:drivers,id'],
            'data.driver_notes' => ['nullable', 'string'],
        ]);

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Calculate total quantity for delivery fee calculation
            $totalQty = 0;
            foreach ($data['meals'] as $meal) {
                $totalQty += intval($meal['normal']) + intval($meal['big']) + intval($meal['small']) + intval($meal['s_small']) + intval($meal['no_rice']);
            }

            // Calculate delivery fee
            $address = CustomerAddressBook::find($data['address_id']);
            $deliveryFee = $this->calculateDeliveryFee($address, $totalQty);

            // Update the order
            $this->order->update([
                'customer_id' => $data['customer_id'],
                'address_id' => $data['address_id'],
                'delivery_date' => \Carbon\Carbon::parse($data['delivery_date']),
                'payment_status_id' => $data['payment_status_id'],
                'payment_method_id' => $data['payment_method_id'],
                'total_amount' => $data['total_amount'],
                'delivery_fee' => $deliveryFee,
                'notes' => $data['notes'],
                'arrival_time' => $data['arrival_time'],
                'driver_id' => $data['driver_id'],
                'driver_route' => $data['driver_route'],
                'backup_driver_id' => $data['backup_driver_id'] ?? 0,
                'driver_notes' => $data['driver_notes'],
            ]);

            // Update or create invoice for this order
            $customer = \App\Models\Customer::find($data['customer_id']);

            $billingAddress = $customer->name . "\n" .
                $address->address_1 . "\n" .
                ($address->address_2 ? $address->address_2 . "\n" : '') .
                $address->mall_or_area;

            // Check if invoice already exists for this order
            $invoice = \App\Models\Invoice::where('order_id', $this->order->id)->first();

            if (!$invoice) {
                // Create new invoice
                \App\Models\Invoice::create([
                    'order_id' => $this->order->id,
                    'invoice_no' => $this->order->invoice_no,
                    'billing_name' => $customer->name,
                    'billing_address' => $billingAddress,
                    'tax_amount' => config('app.tax_rate'),
                    'issue_date' => now(),
                    'due_date' => now()->addDays(30),
                ]);
            }

            // Update or create meals
            $this->order->meals()->delete(); // Remove existing meals
            foreach ($data['meals'] as $meal) {
                $this->order->meals()->create([
                    'meal_id' => $meal['meal_id'],
                    'normal' => $meal['normal'],
                    'big' => $meal['big'],
                    'small' => $meal['small'],
                    's_small' => $meal['s_small'],
                    'no_rice' => $meal['no_rice'],
                ]);
            }

            \DB::commit();

            Notification::make()
                ->success()
                ->title('Order updated successfully')
                ->send();

            $this->redirect('/' . config('filament.path', 'backend') . '/orders');
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error updating order')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);
        $meals = Meal::whereIn('id', collect($this->data['meals'])->pluck('meal_id'))->get()->keyBy('id');

        $temp_meals = [];
        $total_qty = 0;
        foreach ($this->data['meals'] as $meal) {
            if (isset($meal['meal_id']) && isset($meals[$meal['meal_id']])) {
                $meal_qty = $meal['normal'] + $meal['big'] + $meal['small'] + $meal['s_small'] + $meal['no_rice'];
                $total_qty += $meal_qty;
                $temp_meals[] = [
                    'meal_id' => $meal['meal_id'],
                    'name' => $meals[$meal['meal_id']]->name,
                    'normal' => $meal['normal'],
                    'big' => $meal['big'],
                    'small' => $meal['small'],
                    's_small' => $meal['s_small'],
                    'no_rice' => $meal['no_rice'],
                    'qty' => $meal_qty
                ];
            }
        }

        // Calculate delivery fee based on address area and total quantity
        $delivery_fee = $this->calculateDeliveryFee($address, $total_qty);

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

        return [
            'customer_id' => $this->data['customer_id'],
            'customer_name' => $customer?->name ?? '',
            'address_id' => $this->data['address_id'],
            'address' => $address ? $display_address : '',
            'delivery_date' => isset($this->data['delivery_date']) && !empty($this->data['delivery_date'])
                ? date(config('app.date_format'), strtotime($this->data['delivery_date']))
                : '',
            'meals' => $temp_meals,
            'total_amount' => $this->data['total_amount'] ?? '0.00',
            'delivery_fee' => $delivery_fee,
            'notes' => $this->data['notes'] ?? '',
            'arrival_time' => isset($this->data['arrival_time']) && !empty($this->data['arrival_time'])
                ? date('h:i A', strtotime($this->data['arrival_time']))
                : '',
            'driver_id' => $this->data['driver_id'] ?? '',
            'driver_name' => Driver::find($this->data['driver_id'] ?? null)?->name ?? '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => Driver::find($this->data['backup_driver_id'] ?? null)?->name ?? '',
            'driver_notes' => $this->data['driver_notes'] ?? '',
        ];
    }

    public function updatedDataCustomerId(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataAddressId(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDeliveryDate(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataMeals(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataTotalAmount(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataNotes(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataArrivalTime(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverId(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverRoute(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataBackupDriverId(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverNotes(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    private function calculateTotalAmountByMealQty(callable $get, callable $set)
    {
        // Get all meals from the form
        $formData = $this->form->getState();
        $meals = $formData['meals'] ?? [];
        
        $totalMeals = 0;
        foreach ($meals as $meal) {
            $totalMeals += intval($meal['normal'] ?? 0) + 
                          intval($meal['big'] ?? 0) + 
                          intval($meal['small'] ?? 0) + 
                          intval($meal['s_small'] ?? 0) + 
                          intval($meal['no_rice'] ?? 0);
        }
        
        // Get MEAL_PRICE from .env
        $mealPrice = floatval(env('MEAL_PRICE', 8));
        $totalAmount = $totalMeals * $mealPrice;
        
        // Update the form data and refill
        $formData['total_amount'] = number_format($totalAmount, 2);
        $this->form->fill($formData);
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
        $record = $this->getRecord();
        return [
            '/' . config('filament.path', 'backend') . '/orders' => 'Orders',
            '' => $record ? 'Order ' . $record->formatted_id : 'Edit Order',
        ];
    }

    protected function getRecord(): ?Order
    {
        return $this->order;
    }
}
