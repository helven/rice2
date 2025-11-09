<?php

namespace App\Filament\Pages\MealPlan;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Driver;
use App\Models\Meal;
use App\Traits\OrderFormTrait;

class CreateMealPlan extends Page
{
    use InteractsWithForms, OrderFormTrait;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static ?string $navigationLabel = 'New Meal Plan';
    protected static ?string $title = 'New Meal Plan';
    protected static ?string $slug = 'meal-plans/create';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.meal-plan.create-meal-plan';

    public function mount(): void
    {
        $this->form->fill([
            'customer_id' => '',
            'address_id' => '',
            'payment_status_id' => $this->getDefaultPaymentStatusId(),
            'payment_method_id' => '',
            'delivery_date' => [],
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
            'notes' => '',
            'arrival_time' => '',
            'driver_id' => '',
            'driver_route' => '',
            'backup_driver_id' => '',
            'driver_notes' => '',
        ]);

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
            $this->getOrderInformationSection()
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
                        ->extraAttributes([
                            'data-id' => 'delivery_date',
                            'onchange' => "handleDeliveryDateChange(this)"
                        ]),

                    
                    TextInput::make('day_count')
                        ->id('day_count')
                        //->hidden()
                        ->default(1)
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

    protected function getFormActions(): array
    {
        //return [
        //    Action::make('create')
        //        ->label('Save')
        //        ->action(fn() => $this->createMealPlan(false))
        //        ->keyBindings(['mod+s'])
        //        ->color('primary'),
        //    Action::make('createAnother')
        //        ->label('Save & Create another')
        //        ->action(fn() => $this->createMealPlan(true))
        //        ->keyBindings(['mod+shift+s'])
        //        ->color('gray'),
        //    Action::make('cancel')
        //        ->label('Cancel')
        //        ->url('/' . config('filament.path', 'backend') . '/orders')
        //        ->color('gray'),
        //];
    }

    public function create()
    {
        $this->createOrder(false);
    }

    public function createAnother()
    {
        $this->createorder(true);
    }

    protected function createorder(bool $createAnother = false)
    {
        $data = $this->form->getState();

        $this->validate([
            'data.delivery_date' => ['required'],
            'data.meals' => ['required', 'array', 'min:1'],
        ]);

        try {
            \DB::beginTransaction();

            $address = CustomerAddressBook::find($data['address_id']);
            $dates = is_array($data['delivery_date']) ? $data['delivery_date'] : explode(',', $data['delivery_date']);

            // Create 1 order
            $order = \App\Models\Order::create([
                'order_type' => 'meal_plan',
                'order_date' => today(),
                'customer_id' => $data['customer_id'],
                'payment_status_id' => $data['payment_status_id'],
                'payment_method_id' => $data['payment_method_id'],
                'total_amount' => $data['total_amount'],
                'delivery_fee' => 0,
                'notes' => $data['notes'] ?? '',
            ]);

            // Generate order number
            $orderNo = \App\Models\Order::generateOrderNumber(
                $order->id,
                $address->mall_id ?? null,
                today()
            );
            $order->update(['order_no' => $orderNo]);

            // Create N deliveries
            foreach ($dates as $dateString) {
                $date = \Carbon\Carbon::parse(trim($dateString));
                \App\Models\Delivery::create([
                    'deliverable_id' => $order->id,
                    'delivery_date' => $date->format(config('app.date_format')),
                    'arrival_time' => $data['arrival_time'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'] ?? null,
                    'driver_notes' => $data['driver_notes'] ?? '',
                    'address_id' => $data['address_id'],
                    'status_id' => \App\Models\DeliveryStatus::SCHEDULED,
                ]);
            }

            // Create invoice
            $this->createInvoice($order, $address);

            // Create M order_meals
            foreach ($data['meals'] as $meal) {
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

            \DB::commit();

            Notification::make()
                ->success()
                ->title('Meal plan created successfully')
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

            $this->redirect('/' . config('filament.path', 'backend') . '/orders');
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error creating meal plan')
                ->body($e->getMessage())
                ->send();
        }
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

    public function handleAddressChanged($state, callable $set, callable $get)
    {
        // CreateMealPlan specific JavaScript
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

    protected function getMealPlanTotalAmountField()
    {
        return TextInput::make('total_amount')
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
