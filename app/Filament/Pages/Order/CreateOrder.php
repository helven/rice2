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
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.order.create-order';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'customer_id' => '',
            'address_id' => '',
            'delivery_start_date' => '',
            'delivery_end_date' => '',
            'delivery_date_range' => '',
            'meal_id' => '',
            'meals' => [['normal_rice' => 0, 'small_rice' => 0, 'no_rice' => 0, 'vegi' => 0]],
            'total_amount' => 0.00,
            'notes' => '',
            'arrival_time' => '',
            'driver_id' => '',
            'driver_route' => '',
            'backup_driver_id' => '',
            'backup_driver_route' => ''
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
        DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->native(false));
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
                                //->dehydrated(fn (callable $get): bool => filled($get('customer_id')))
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
                        ->timezone('Asia/Kuala_Lumpur')
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
                        ->addAction(
                            fn ($action) => $action
                                ->label('Add Meal')
                                ->extraAttributes([
                                    'class' => '',
                                ])
                        )
                        ->schema([
                            Grid::make(5)
                                ->schema([
                                    Select::make('meal_id')
                                        ->label('Meal')
                                        ->placeholder('Select Meal')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->options(Meal::query()->pluck('name', 'id')),

                                    TextInput::make('normal_rice')
                                        ->label('Normal Rice')
                                        ->numeric()
                                        ->default(0)
                                        ->extraInputAttributes(['min' => 0, 'max' => 100])
                                        ->step(1)
                                        ->rules(['required', 'integer', 'min:0', 'max:100']),

                                    TextInput::make('small_rice')
                                        ->label('Small Rice')
                                        ->numeric()
                                        ->default(0)
                                        ->extraInputAttributes(['min' => 0, 'max' => 100])
                                        ->step(1)
                                        ->rules(['required', 'integer', 'min:0', 'max:100']),
                                    
                                    TextInput::make('no_rice')
                                        ->label('No Rice')
                                        ->numeric()
                                        ->default(0)
                                        ->extraInputAttributes(['min' => 0, 'max' => 100])
                                        ->step(1)
                                        ->rules(['required', 'integer', 'min:0', 'max:100']),
                                    
                                    TextInput::make('vegi')
                                        ->label('Vegi')
                                        ->numeric()
                                        ->default(0)
                                        ->extraInputAttributes(['min' => 0, 'max' => 100])
                                        ->step(1)
                                        ->rules(['required', 'integer', 'min:0', 'max:100']),
                                ])
                            ]),
                    TextInput::make('total_amount')
                        ->label('Total')
                        ->numeric()
                        ->default(0.00)
                        ->prefix('RM')
                        ->rules(['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'])
                        ->placeholder('0.00'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(5)
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
                                ->timezone('Asia/Kuala_Lumpur')
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
                                    $set('route', null);
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
                                    $set('route', null);
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
            'data.delivery_date_range' => ['required', 'string'],
            'data.arrival_time' => ['required'],
            'data.driver_id' => ['required', 'exists:drivers,id'],
            'data.driver_route' => ['required', 'string'],
            'data.backup_driver_id' => ['nullable', 'exists:drivers,id'],
            'data.backup_driver_route' => ['nullable', 'string'],
            'data.meals' => ['required', 'array', 'min:1'],
            'data.meals.*.meal_id' => ['required', 'exists:meals,id'],
            'data.meals.*.normal_rice' => ['required', 'integer', 'min:0', 'max:100'],
            'data.meals.*.small_rice' => ['required', 'integer', 'min:0', 'max:100'],
            'data.meals.*.no_rice' => ['required', 'integer', 'min:0', 'max:100'],
            'data.meals.*.vegi' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        
        // Parse the date range
        [$startDate, $endDate] = explode(' - ', $data['delivery_date_range']);
        $startDate = \Carbon\Carbon::parse(str_replace('/', '-', $startDate));
        $endDate = \Carbon\Carbon::parse(str_replace('/', '-', $endDate));
        
        // Create a collection of dates between start and end
        $dates = collect();
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates->push($date->copy());
        }
        
        // Begin transaction
        \DB::beginTransaction();
        
        try {
            // Create an order for each date
            foreach ($dates as $date) {
                $order = \App\Models\Order::create([
                    'customer_id' => $data['customer_id'],
                    'address_id' => $data['address_id'],
                    'delivery_date' => $date->setTimeFromTimeString($data['arrival_time']),
                    'total_amount' => $data['total_amount'],
                    'notes' => $data['notes'],
                    'driver_id' => $data['driver_id'],
                    'driver_route' => $data['driver_route'],
                    'backup_driver_id' => $data['backup_driver_id'] ?? 0,
                    'backup_driver_route' => $data['backup_driver_route'] ?? '',
                ]);

                // Create order meals
                foreach ($data['meals'] as $meal) {
                    \App\Models\OrderMeal::create([
                        'order_id' => $order->id,
                        'meal_id' => $meal['meal_id'],
                        'normal_rice' => $meal['normal_rice'],
                        'small_rice' => $meal['small_rice'],
                        'no_rice' => $meal['no_rice'],
                        'vegi' => $meal['vegi'],
                    ]);
                }
            }
            
            \DB::commit();
            
            // Redirect to orders list with success message
            Notification::make()
                ->success()
                ->title('Orders created successfully')
                ->send();
                
            $this->redirect('/admin/orders');
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error creating orders')
                ->body($e->getMessage())
                ->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/orders' => 'Orders',
            '' => 'New Order',
        ];
    }
}