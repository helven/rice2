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
use App\Models\Order;

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
            'meals' => $this->order->meals->map(function($meal) {
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
            'backup_driver_id' => ($this->order->backup_driver_id !== 0)?$this->order->backup_driver_id:'',
            'backup_driver_route' => $this->order->backup_driver_route,
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
        //DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->native(false));
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

                    DateTimePicker::make('delivery_date')
                        ->label('Delivery Date')
                        ->required()
                        //->minDate(\Carbon\Carbon::now())
                        ->timezone('Asia/Kuala_Lumpur')
                        ->displayFormat('Y/m/d') // 12-hour with AM/PM (K = AM/PM)
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
                                ->live()
                                ->columnSpan(2),
                            TextInput::make('normal')
                                ->label('Normal')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(1000)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('normal', $state);
                                })
                                ->step(1)
                                ->rules(['required', 'integer', 'min:0', 'max:1000']),
                            TextInput::make('big')
                                    ->label('Big')
                                    ->numeric()
                                    ->default(0)
                                    ->extraInputAttributes(['min' => 0, 'max' => 1000])
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $state = (int)ltrim($state, '0') ?: 0;
                                        $set('big', $state);
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
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('small', $state);
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
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('s_small', $state);
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
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $state = (int)ltrim($state, '0') ?: 0;
                                    $set('no_rice', $state);
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
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('backup_driver_route', null);
                                }),
                            Select::make('backup_driver_route')
                                ->label('Route')
                                ->placeholder('Select Route') 
                                ->searchable()
                                ->live()
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
            'data.backup_driver_route' => ['nullable', 'string'],
            'data.driver_notes' => ['nullable', 'string'],
        ]);

        try {
            // Begin transaction
            \DB::beginTransaction();
            
            // Update the order
            $this->order->update([
                'customer_id' => $data['customer_id'],
                'address_id' => $data['address_id'],
                'delivery_date' => \Carbon\Carbon::parse($data['delivery_date']),
                'total_amount' => $data['total_amount'],
                'notes' => $data['notes'],
                'arrival_time' => $data['arrival_time'],
                'driver_id' => $data['driver_id'],
                'driver_route' => $data['driver_route'],
                'backup_driver_id' => $data['backup_driver_id'] ?? 0,
                'backup_driver_route' => $data['backup_driver_route'] ?? '',
                'driver_notes' => $data['driver_notes'],
            ]);

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

            $this->redirect('/'.config('filament.path', 'backend').'/orders');
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
        foreach($this->data['meals'] as $meal){
            if (isset($meal['meal_id']) && isset($meals[$meal['meal_id']])) {
                $temp_meals[] = [
                    'meal_id' => $meal['meal_id'],
                    'name' => $meals[$meal['meal_id']]->name,
                    'normal' => $meal['normal'],
                    'big' => $meal['big'],
                    'small' => $meal['small'],
                    's_small' => $meal['s_small'],
                    'no_rice' => $meal['no_rice'],
                    'qty' => $meal['normal'] + $meal['big'] + $meal['small'] + $meal['s_small'] + $meal['no_rice']
                ];
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

        return [
            'customer_id' => $this->data['customer_id'],
            'customer_name' => $customer?->name ?? '',
            'address_id' => $this->data['address_id'],
            'address' => $address ? $display_address : '',
            'delivery_date' => isset($this->data['delivery_date']) && !empty($this->data['delivery_date']) 
                ? date('Y-m-d', strtotime($this->data['delivery_date'])) 
                : '',
            'meals' => $temp_meals,
            'total_amount' => $this->data['total_amount'] ?? '0.00',
            'notes' => $this->data['notes'] ?? '',
            'arrival_time' => isset($this->data['arrival_time']) && !empty($this->data['arrival_time']) 
                ? date('h:i A', strtotime($this->data['arrival_time'])) 
                : '',
            'driver_id' => $this->data['driver_id'] ?? '',
            'driver_name' => Driver::find($this->data['driver_id'] ?? null)?->name ?? '',
            'driver_route' => $this->data['driver_route'] ?? '',
            'backup_driver_id' => $this->data['backup_driver_id'] ?? '',
            'backup_driver_name' => Driver::find($this->data['backup_driver_id'] ?? null)?->name ?? '',
            'backup_driver_route' => $this->data['backup_driver_route'] ?? '',
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

    public function updatedDataBackupDriverRoute(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function updatedDataDriverNotes(): void
    {
        $this->modalData = $this->getFormattedData();
    }

    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();
        return [
            '/'.config('filament.path', 'backend').'/orders' => 'Orders',
            '' => $record ? 'Order '.$record->formatted_id : 'Edit Order',
        ];
    }

    protected function getRecord(): ?Order
    {
        return $this->order;
    }
}