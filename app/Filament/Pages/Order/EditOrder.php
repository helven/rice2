<?php

namespace App\Filament\Pages\Order;

use App\Models\CustomerAddressBook;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Area;
use App\Models\AttrPaymentMethod;
use App\Traits\OrderFormTrait;

class EditOrder extends Page
{
    use InteractsWithForms, OrderFormTrait;

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

    // EditOrder specific handlers
    public function onCustomerChanged($state, callable $set, callable $get)
    {
        // EditOrder specific JavaScript
        $orderId = $get('id');
        $this->js('
            setTimeout(() => {
                const customerId = ' . json_encode($state) . ';
                const addressId = null;
                const orderId = ' . json_encode($orderId) . ';
                if (typeof fetchExistingDeliveryDates === "function") {
                    fetchExistingDeliveryDates(customerId, addressId, orderId);
                }
            }, 100);
        ');
    }

    public function onAddressChanged($state, callable $set, callable $get)
    {
        // EditOrder specific JavaScript
        $customerId = $get('customer_id');
        $orderId = $get('id');
        $this->js('
            setTimeout(() => {
                const customerId = ' . json_encode($customerId) . ';
                const addressId = ' . json_encode($state) . ';
                const orderId = ' . json_encode($orderId) . ';
                if (typeof fetchExistingDeliveryDates === "function") {
                    fetchExistingDeliveryDates(customerId, addressId, orderId);
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
                    TextInput::make('id')
                        ->label('Order No')
                        ->readonly(),
                    $this->getCustomerAddressGrid(),
                    $this->getPaymentGrid(),
                    
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
                    Grid::make(2)->schema([
                        DateTimePicker::make('arrival_time')
                            ->withoutDate()
                            ->label('Arrival Time')
                            ->placeholder('Select Arrival Time')
                            ->required()
                            ->displayFormat('h:i A')
                            ->format('H:i')
                            ->withoutSeconds()
                            ->live()
                    ]),
                    $this->getDriverGrid(),
                    $this->getBackupDriverGrid(),
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
            <?php echo e($address->name); ?><br />
            <?php echo e($address->address_1); ?><br />
            <?php echo ($address->address_2) ? e($address->address_2) . '<br />' : ""; ?>
            <?php echo e($address->postcode); ?> <?php echo e($address->city); ?>
            <?php
            $display_address = trim(ob_get_clean());
        }

        $driver = Driver::find($this->data['driver_id'] ?? null);
        $backupDriver = Driver::find($this->data['backup_driver_id'] ?? null);

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
            'driver_name' => $driver?->name ?? '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => $backupDriver?->name ?? '',
            'driver_notes' => $this->data['driver_notes'] ?? '',
        ];
    }

    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'updatedData')) {
            $this->modalData = $this->getFormattedData();
            return;
        }
        
        return parent::__call($method, $parameters);
    }

    private function calculateTotalAmountByMealQty(callable $set, callable $get)
    {
        // Get all meals from the form without validation to avoid validation errors
        $formData = $this->form->getRawState();
        $meals = $formData['meals'] ?? [];
        
        $totalMeals = 0;
        foreach ($meals as $meal) {
            $totalMeals += intval($meal['normal'] ?? 0) + 
                          intval($meal['big'] ?? 0) + 
                          intval($meal['small'] ?? 0) + 
                          intval($meal['s_small'] ?? 0) + 
                          intval($meal['no_rice'] ?? 0);
        }
        
        // Get MEAL_PRICE from config
        $mealPrice = config('app.meal_price', 8.00);
        $totalAmount = $totalMeals * $mealPrice;
        
        // Update the form data and refill
        $formData['total_amount'] = number_format($totalAmount, 2);
        $this->form->fill($formData);
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