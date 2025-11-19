<?php

namespace App\Filament\Pages\Order;

use App\Models\CustomerAddressBook;
use App\Services\DeliveryService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Delivery;
use App\Models\Meal;
use App\Models\Order;
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

    public ?Order $order = null;

    public function mount($id): void
    {
        $this->order = Order::with(['meals', 'customer', 'deliveries'])->findOrFail($id);

        // Get delivery data from Order model
        $delivery = $this->order->getDelivery();

        $this->form->fill([
            'id' => $this->order->formatted_id,
            'order_no' => $this->order->order_no,
            'customer_id' => $this->order->customer_id,
            'address_id' => $delivery?->address_id,
            'delivery_date' => $delivery?->delivery_date?->format('Y-m-d'),
            'payment_status_id' => $this->order->payment_status_id,
            'payment_method_id' => $this->order->payment_method_id,
            'meals' => $this->order->meals->map(function ($meal) {
                return [
                    'meal_id' => $meal->meal_id,
                    'normal' => $meal->normal,
                    'big' => $meal->big,
                    'small' => $meal->small,
                    'no_rice' => $meal->no_rice,
                ];
            })->toArray(),
            'total_amount' => $this->order->total_amount,
            'notes' => $this->order->notes,
            // Load driver data from deliveries table
            'arrival_time' => $delivery?->arrival_time ?? '',
            'driver_id' => ($delivery?->driver_id && $delivery->driver_id > 0) ? $delivery->driver_id : null,
            'driver_route' => $delivery?->driver_route ?? '',
            'backup_driver_id' => ($delivery?->backup_driver_id && $delivery->backup_driver_id > 0) ? $delivery->backup_driver_id : null,
            'driver_notes' => $delivery?->driver_notes ?? '',
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
            $this->getOrderInformationSection()
                ->schema([
                    TextInput::make('order_no')
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
                ->extraAttributes(['data-id' => 'meals_by_date'])
                ->schema([
                    $this->getMealsRepeater(),
                    $this->getTotalAmountField(),
                    $this->getNotesField()
                ]),
                
            $this->getDriverSection(),
        ];
    }

    public function save()
    {
        // Validate the form data
        $data = $this->form->getState();

        $this->validate(array_merge($this->getCommonValidationRules(), [
            'data.delivery_date' => ['required', 'string'],
            'data.meals' => ['required', 'array', 'min:1'],
            'data.meals.*.meal_id' => ['required', 'exists:meals,id'],
            'data.meals.*.normal' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.big' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.small' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.no_rice' => ['required', 'integer', 'min:0', 'max:1000'],
        ]));

        try {
            \DB::beginTransaction();

            $totalQty = $this->calculateTotalQuantity($data['meals']);
            $address = CustomerAddressBook::find($data['address_id']);
            $deliveryFee = $this->calculateDeliveryFee($address, $totalQty);

            // Update order (customer and financial data only)
            $this->order->update([
                'order_type' => 'single',
                'order_date' => $data['delivery_date'],
                'customer_id' => $data['customer_id'],
                'payment_status_id' => $data['payment_status_id'],
                'payment_method_id' => $data['payment_method_id'],
                'total_amount' => $data['total_amount'],
                'delivery_fee' => $deliveryFee,
                'notes' => $data['notes'],
            ]);

            // Update delivery data using DeliveryService
            $deliveryService = new DeliveryService();
            $deliveryService->storeDeliveryData($this->order, $data);

            // Update meals
            $this->order->meals()->delete();
            foreach ($data['meals'] as $meal) {
                $this->order->meals()->create([
                    'meal_id' => $meal['meal_id'],
                    'normal' => $meal['normal'],
                    'big' => $meal['big'],
                    'small' => $meal['small'],
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

    public function handleAddressChanged($state, callable $set, callable $get)
    {
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

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);
        $meals = Meal::whereIn('id', collect($this->data['meals'])->pluck('meal_id'))->get()->keyBy('id');

        $temp_meals = [];
        $total_qty = 0;
        foreach ($this->data['meals'] as $meal) {
            if (isset($meal['meal_id']) && isset($meals[$meal['meal_id']])) {
                $meal_qty = $meal['normal'] + $meal['big'] + $meal['small'] + $meal['no_rice'];
                $total_qty += $meal_qty;
                $temp_meals[] = [
                    'meal_id' => $meal['meal_id'],
                    'name' => $meals[$meal['meal_id']]->name,
                    'normal' => $meal['normal'],
                    'big' => $meal['big'],
                    'small' => $meal['small'],
                    'no_rice' => $meal['no_rice'],
                    'qty' => $meal_qty
                ];
            }
        }

        // Calculate delivery fee based on address area and total quantity
        $delivery_fee = $this->calculateDeliveryFee($address, $total_qty);

        $display_address = $this->getFormattedAddressDisplay($address);

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

    protected function getRecord(): ?Order
    {
        return $this->order;
    }
}