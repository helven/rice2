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
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Meal;

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

    public function mount(): void
    {
        // Get first active meal for default
        $defaultMeal = Meal::where('status_id', 1)->first();
        $defaultMealId = $defaultMeal ? $defaultMeal->id : '';
        
        $this->form->fill([
            'order_no' => '',
            'customer_id' => '',
            'address_id' => '',
            'delivery_date_range' => '',
            'meals_by_date' => [],
            'total_amount' => 0.00,
            'notes' => '',
            'arrival_time' => '',
            'driver_id' => '',
            'driver_route' => '',
            'backup_driver_id' => '',
            'backup_driver_route' => '',
            'driver_notes' => '',
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
                    TextInput::make('order_no')
                        ->required()
                        ->maxLength(64),
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_id')
                                ->label('Customer Name')
                                ->placeholder('Select Customer')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(Customer::query()->pluck('name', 'id'))
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('address_id', null);
                                }),

                            Select::make('address_id')
                                ->label('Delivery Location')
                                ->placeholder('Select Company or City')
                                ->required()
                                ->searchable()
                                ->allowHtml()
                                ->disabled(fn (callable $get): bool => blank($get('customer_id')))
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
                                            ob_start();?>
<div class="hidden"><?php echo "{$address->name}|{$address->city}";?></div>
<span class="font-bold"><?php echo $address->name;?></span><?php echo $address->is_default?'<span class="italic text-xs text-gray-400"> (default)</span>':"";?>
<div><?php echo $address->address_1;?><br />
<?php echo ($address->address_2)?$address->address_2.'<br />':"";?>
<?php echo $address->postcode;?> <?php echo $address->city;?></div>
<?php
                                            $displayAddress = trim(ob_get_clean());
                                            return [$address->id => $displayAddress];
                                        });
                                })
                        ]),
                    DateRangePicker::make('delivery_date_range')
                        ->label('Delivery Date')
                        ->required()
                        ->minDate(\Carbon\Carbon::now())
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (empty($state)) {
                                $set('meals_by_date', []);
                                return;
                            }

                            [$startDate, $endDate] = explode(' - ', $state);
                            $startDate = \Carbon\Carbon::parse(str_replace('/', '-', $startDate));
                            $endDate = \Carbon\Carbon::parse(str_replace('/', '-', $endDate));

                            $meals_by_date = [];
                            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                                $meals_by_date[] = [
                                    'date' => $date->format('Y-m-d'),
                                    'meals' => [
                                        [
                                            'meal_id' => '',
                                            'normal_rice' => 0,
                                            'small_rice' => 0,
                                            'no_rice' => 0,
                                            'vegi' => 0
                                        ]
                                    ]
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
                                \Carbon\Carbon::parse($currentItem['date'])->format('d M Y')
                            );
                        }),
                    Repeater::make('meals')
                        ->label('Meals')
                        ->defaultItems(1)
                        ->reorderable(false)
                        ->deletable(true)
                        ->cloneable()
                        ->columns(6)
                        ->addAction(
                            fn ($action) => $action
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
                                ->columnSpan(2),
                            TextInput::make('normal_rice')
                                ->label('Normal')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->required(),
                            TextInput::make('small_rice')
                                ->label('Small')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->required(),
                            TextInput::make('no_rice')
                                ->label('No Rice')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->required(),
                            TextInput::make('vegi')
                                ->label('Vegi')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->required()
                        ]),

                    TextInput::make('total_amount')
                        ->label('Total')
                        ->placeholder('0.00')
                        ->numeric()
                        ->default(0.00)
                        ->prefix('RM')
                        ->rules(['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'])
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $state = (int)ltrim($state, '0') ?: 0;
                            $set('total_amount', $state);
                        }),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(5)
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
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('driver_route', null);
                                }),
                            Select::make('driver_route')
                                ->label('Route')
                                ->placeholder('Select Route')
                                ->required()
                                ->searchable()
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
                                ->disabled(fn (callable $get): bool => blank($get('driver_id')))
                        ]),
                        Grid::make(2)
                        ->schema([
                            Select::make('backup_driver_id')
                                ->label('Backup Driver')
                                ->placeholder('Select Backup Driver')
                                ->searchable()
                                ->preload()
                                ->options(Driver::query()->pluck('name', 'id'))
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('backup_driver_route', null);
                                }),
                            Select::make('backup_driver_route')
                                ->label('Route')
                                ->placeholder('Select Route')
                                ->searchable()
                                ->options(function (callable $get) {
                                    $driverId = $get('backup_driver_id');
                            
                                    if (blank($driverId)) {
                                        return [];
                                    }
                            
                                    $driver = \App\Models\Driver::find($driverId);
                                    if (!$driver || !$driver->route) {
                                        return [];
                                    }
                                    return collect($driver->route)->pluck('route_name', 'route_name');
                                })
                                ->disabled(fn (callable $get): bool => blank($get('driver_id')))
                        ]),
                        Textarea::make('driver_notes')
                            ->label('Notes')
                            ->rows(5)
                ]),
        ];
    }

    public function create()
    {
        // Validate the form data
        $data = $this->form->getState();
        
        $this->validate([
            'data.order_no' => ['required', 'string'],
            'data.customer_id' => ['required', 'exists:customers,id'],
            'data.address_id' => ['required', 'exists:customer_address_books,id'],
            'data.delivery_date_range' => ['required', 'string'],
            'data.arrival_time' => ['required'],
            'data.meals_by_date' => ['required', 'array', 'min:1'],
            'data.driver_id' => ['required', 'exists:drivers,id'],
            'data.driver_route' => ['required', 'string'],
            'data.backup_driver_id' => ['nullable', 'exists:drivers,id'],
            'data.backup_driver_route' => ['nullable', 'string'],
            'data.driver_notes' => ['nullable', 'string'],
        ]);

        try {
            // Begin transaction
            \DB::beginTransaction();
            
            // Create an order for each date
            $order_no_ctr = 1;
            foreach ($data['meals_by_date'] as $date => $dateData) {
                // Skip if no meals for this date
                if (empty($dateData['meals'])) {
                    continue;
                }

                $order = \App\Models\Order::create([
                    'order_no' => $data['order_no'].'-'.str_pad($order_no_ctr, 2, '0', STR_PAD_LEFT),
                    'customer_id' => $data['customer_id'],
                    'address_id' => $data['address_id'],
                    'delivery_date' => $dateData['date'],
                    'total_amount' => $dateData['total_amount'],
                    'notes' => $dateData['notes'] ?? '',
                    'arrival_time' => $data['arrival_time'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'],
                    'backup_driver_route' => $data['backup_driver_route'] ?? '',
                    'driver_notes' => $data['driver_notes'] ?? '',
                ]);

                // Create order meals for this date
                foreach ($dateData['meals'] as $meal) {
                    \App\Models\OrderMeal::create([
                        'order_id' => $order->id,
                        'meal_id' => $meal['meal_id'],
                        'normal_rice' => $meal['normal_rice'],
                        'small_rice' => $meal['small_rice'],
                        'no_rice' => $meal['no_rice'],
                        'vegi' => $meal['vegi'],
                    ]);
                }

                $order_no_ctr++;
            }

            \DB::commit();
            
            Notification::make()
                ->success()
                ->title('Orders created successfully')
                ->send();

            $this->redirect('/'.config('filament.path', 'backend').'/orders');
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
                    $meal['normal_rice'] + 
                    $meal['small_rice'] + 
                    $meal['no_rice'] + 
                    $meal['vegi']
                );
            }
        }
        return $total;
    }

    public function getFormattedData()
    {
        $customer = Customer::find($this->data['customer_id'] ?? null);
        $address = CustomerAddressBook::find($this->data['address_id'] ?? null);
        
        $temp_meals = [];
        
        // Handle meals_by_date structure (for Create Order)
        if (isset($this->data['meals_by_date']) && is_array($this->data['meals_by_date'])) {
            foreach ($this->data['meals_by_date'] as $date => $dateData) {
                if (isset($dateData['meals'])) {
                    $meal_ids = collect($dateData['meals'])->pluck('meal_id')->toArray();
                    $meals = Meal::whereIn('id', $meal_ids)->get()->keyBy('id');
                    
                    foreach ($dateData['meals'] as $meal) {
                        if (isset($meal['meal_id']) && isset($meals[$meal['meal_id']])) {
                            $temp_meals[] = [
                                'meal_id' => $meal['meal_id'],
                                'name' => $meals[$meal['meal_id']]->name,
                                'normal_rice' => $meal['normal_rice'],
                                'small_rice' => $meal['small_rice'],
                                'no_rice' => $meal['no_rice'],
                                'vegi' => $meal['vegi'],
                                'qty' => intval($meal['normal_rice']) + intval($meal['small_rice']) + 
                                       intval($meal['no_rice']) + intval($meal['vegi']),
                            ];
                        }
                    }
                }
            }
        }

        $display_address = '';
        if($address){
            ob_start();?>
<?php echo $address->name;?><br />
<?php echo $address->address_1;?><br />
<?php echo ($address->address_2)?$address->address_2.'<br />':"";?>
<?php echo $address->postcode;?> <?php echo $address->city;?>
<?php
            $display_address = trim(ob_get_clean());
        }
        
        $delivery_dates = '';

        // Parse the date range
        if($this->data['delivery_date_range'] !== '') {
            [$startDate, $endDate] = explode(' - ', $this->data['delivery_date_range']);
            $startDate = \Carbon\Carbon::parse(str_replace('/', '-', $startDate));
            $endDate = \Carbon\Carbon::parse(str_replace('/', '-', $endDate));

            // Create a collection of dates between start and end
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $delivery_dates .= ($delivery_dates != ''?', ':'').$date->format('Y-m-d');
            }
        }

        return [
            'order_no' => $this->data['order_no'],
            'customer_id' => $this->data['customer_id'],
            'customer_name' => $customer ? $customer->name : '',
            'address_id' => $this->data['address_id'],
            'address' => $address ? $display_address : '',
            'delivery_date' => $delivery_dates,
            'meals' => $temp_meals,
            'total_amount' => $this->data['total_amount'] ?? '0.00',
            'notes' => $this->data['notes'] ?? '',
            'arrival_time' => isset($this->data['arrival_time']) && !empty($this->data['arrival_time']) 
                ? date('h:i A', strtotime($this->data['arrival_time'])) 
                : '',
            'driver_id' => $this->data['driver_id'] ?? '',
            'driver_name' => Driver::find($this->data['driver_id'] ?? null) ? Driver::find($this->data['driver_id'] ?? null)->name : '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => Driver::find($this->data['backup_driver_id'] ?? null) ? Driver::find($this->data['backup_driver_id'] ?? null)->name : '',
            'backup_driver_route' => $this->data['backup_driver_route'] ?? '',
            'driver_notes' => $this->data['driver_notes'] ?? '',
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/orders' => 'Orders',
            '' => 'New Order',
        ];
    }
}