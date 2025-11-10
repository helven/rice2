<?php

namespace App\Filament\Pages\MealPlan;

use App\Services\DeliveryService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Driver;
use App\Models\Meal;
use App\Models\Order;
use App\Traits\OrderFormTrait;

class EditMealPlan extends Page
{
    use InteractsWithForms, OrderFormTrait;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?string $title = 'Edit Meal Plan';
    protected static ?string $slug = 'meal-plans/{id}/edit';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.meal-plan.edit-meal-plan';

    public ?Order $order = null;
    public bool $disableDeliveryDate = false;

    public function mount($id): void
    {
        $this->order = Order::with(['meals', 'customer', 'deliveries'])->findOrFail($id);

        $deliveries = $this->order->deliveries;
        $firstDelivery = $deliveries->first();
        $deliveryDates = $deliveries->pluck('delivery_date')->map(fn($date) => $date->format(config('app.date_format')))->toArray();

        $this->form->fill([
            'id' => $this->order->formatted_id,
            'order_no' => $this->order->order_no,
            'customer_id' => $this->order->customer_id,
            'address_id' => $firstDelivery?->address_id,
            'delivery_date' => $deliveryDates,
            'day_count' => count($deliveryDates),
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
            'arrival_time' => $firstDelivery?->arrival_time ?? '',
            'driver_id' => ($firstDelivery?->driver_id && $firstDelivery->driver_id > 0) ? $firstDelivery->driver_id : null,
            'driver_route' => $firstDelivery?->driver_route ?? '',
            'backup_driver_id' => ($firstDelivery?->backup_driver_id && $firstDelivery->backup_driver_id > 0) ? $firstDelivery->backup_driver_id : null,
            'driver_notes' => $firstDelivery?->driver_notes ?? '',
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
                        ->label('Delivery Dates')
                        ->multiplePicker()
                        ->format(config('app.date_format'))
                        ->displayFormat(config('app.date_format'))
                        ->conjunction(', ')
                        ->minDate(fn() => today())
                        ->required()
                        ->disabled($this->disableDeliveryDate)
                        ->live()
                        ->debounce(0)
                        ->extraAttributes([
                            'data-id' => 'delivery_date',
                            'onchange' => "handleDeliveryDateChange(this)"
                        ]),

                    
                    TextInput::make('day_count')
                        ->id('day_count')
                        //->hidden()
                        ->extraAttributes(['data-id' => 'day_count'])
                        ->dehydrated(false)
                ]),

            Section::make('Meals')
                ->collapsible()
                ->schema([
                    $this->getMealsRepeater('meal_plan'),
                    $this->getMealPlanTotalAmountField(),
                    $this->getNotesField()
                ]),
                
            $this->getDriverSection(),
        ];
    }

    public function save()
    {
        $data = $this->form->getState();

        $this->validate(array_merge($this->getCommonValidationRules(), [
            'data.delivery_date' => ['required'],
            'data.meals' => ['required', 'array', 'min:1'],
            'data.meals.*.meal_id' => ['required', 'exists:meals,id'],
            'data.meals.*.normal' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.big' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.small' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.s_small' => ['required', 'integer', 'min:0', 'max:1000'],
            'data.meals.*.no_rice' => ['required', 'integer', 'min:0', 'max:1000'],
        ]));

        try {
            \DB::beginTransaction();

            // Update order
            $this->order->update([
                'order_type' => 'meal_plan',
                'order_date' => $this->order->order_date,
                'customer_id' => $data['customer_id'],
                'payment_status_id' => $data['payment_status_id'],
                'payment_method_id' => $data['payment_method_id'],
                'total_amount' => $data['total_amount'],
                'delivery_fee' => 0,
                'notes' => $data['notes'] ?? '',
            ]);

            // Update deliveries
            if (!$this->disableDeliveryDate) {
                // Dates can be changed - sync deliveries by date
                $dates = is_array($data['delivery_date']) ? $data['delivery_date'] : explode(',', $data['delivery_date']);
                $existingDeliveries = $this->order->deliveries->keyBy(fn($d) => $d->delivery_date->format(config('app.date_format')));
                
                $deliveryData = [
                    'arrival_time' => $data['arrival_time'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'] ?? null,
                    'driver_notes' => $data['driver_notes'] ?? '',
                    'address_id' => $data['address_id'],
                ];
                
                // Update or create deliveries for new dates
                foreach ($dates as $date) {
                    if (isset($existingDeliveries[$date])) {
                        $existingDeliveries[$date]->update($deliveryData);
                        unset($existingDeliveries[$date]);
                    } else {
                        $this->order->deliveries()->create(array_merge($deliveryData, ['delivery_date' => $date]));
                    }
                }
                
                // Delete deliveries for removed dates
                foreach ($existingDeliveries as $delivery) {
                    $delivery->delete();
                }
            } else {
                // Dates are disabled - just update other fields for each delivery
                foreach ($this->order->deliveries as $delivery) {
                    $delivery->update([
                        'arrival_time' => $data['arrival_time'],
                        'driver_id' => $data['driver_id'],
                        'driver_route' => $data['driver_route'],
                        'backup_driver_id' => $data['backup_driver_id'] ?? null,
                        'driver_notes' => $data['driver_notes'] ?? '',
                        'address_id' => $data['address_id'],
                    ]);
                }
            }
            
            // Update meals
            $this->order->meals()->delete();
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
                ->title('Meal plan updated successfully')
                ->send();

            $this->redirect('/' . config('filament.path', 'backend') . '/orders');
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error updating meal plan')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function handleAddressChanged($state, callable $set, callable $get)
    {
        // EditMealPlan specific JavaScript
        $customerId = $get('customer_id');
        $this->js('
            setTimeout(() => {
                const customerId = ' . json_encode($customerId) . ';
                const addressId = ' . json_encode($state) . ';
                const excludeOrderId = ' . json_encode($this->order->id) . ';
                if (typeof fetchExistingDeliveryDates === "function") {
                    fetchExistingDeliveryDates(customerId, addressId, excludeOrderId);
                }
            }, 100);
        ');
    }

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);
        $driver = Driver::find($this->data['driver_id'] ?? null);
        $backupDriver = Driver::find($this->data['backup_driver_id'] ?? null);

        $formatted_meals = [];
        $total_qty = 0;
        
        if (isset($this->data['meals'])) {
            $mealIds = collect($this->data['meals'])->pluck('meal_id')->filter()->toArray();
            $allMeals = Meal::whereIn('id', array_unique($mealIds))->get()->keyBy('id');
            
            foreach ($this->data['meals'] as $meal) {
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
        }

        $delivery_date = '';
        if (!empty($this->data['delivery_date'])) {
            $dateStrings = is_array($this->data['delivery_date']) ? $this->data['delivery_date'] : explode(',', $this->data['delivery_date']);
            foreach ($dateStrings as $dateString) {
                $date = \Carbon\Carbon::parse(trim($dateString));
                $delivery_date .= ($delivery_date != '' ? ', ' : '') . $date->format(config('app.date_format'));
            }
        }

        return [
            'customer_id' => $this->data['customer_id'],
            'customer_name' => $customer ? $customer->name : '',
            'address_id' => $this->data['address_id'],
            'address' => $address ? $this->getFormattedAddressDisplay($address) : '',
            'delivery_date' => $delivery_date,
            'meals' => $formatted_meals,
            'total_amount' => $this->data['total_amount'] ?? 0,
            'delivery_fee' => 0,
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

    protected function getMealPlanTotalAmountField()
    {
        return \Filament\Forms\Components\TextInput::make('total_amount')
            ->label(function (callable $get) {
                $dates = $get('delivery_date');
                $dayCount = 0;
                
                if (!empty($dates)) {
                    $dateArray = is_array($dates) ? $dates : explode(',', $dates);
                    $dayCount = count($dateArray);
                }
                
                return $dayCount > 0 ? "Total ({$dayCount} day" . ($dayCount > 1 ? 's' : '') . ")" : 'Total';
            })
            ->extraInputAttributes([
                'data-id' => 'total_amount'
            ])
            ->placeholder('0.00')
            ->numeric()
            ->default(0.00)
            ->prefix('RM')
            ->live()
            ->rules(['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/']);
    }
}
