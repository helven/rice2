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

    public function mount($id): void
    {
        $this->order = Order::with(['meals', 'customer', 'address', 'driver', 'backupDriver'])->findOrFail($id);
        
        $this->form->fill([
            'customer_id' => $this->order->customer_id,
            'address_id' => $this->order->address_id,
            'delivery_date' => $this->order->delivery_date,
            'notes' => $this->order->notes,
            'total_amount' => $this->order->total_amount,
            'arrival_time' => $this->order->delivery_date->format('H:i'),
            'driver_id' => $this->order->driver_id,
            'driver_route' => $this->order->driver_route,
            'backup_driver_id' => $this->order->backup_driver_id,
            'backup_driver_route' => $this->order->backup_driver_route,
            'meals' => $this->order->meals->map(function($meal) {
                return [
                    'meal_id' => $meal->meal_id,
                    'normal_rice' => $meal->normal_rice,
                    'small_rice' => $meal->small_rice,
                    'no_rice' => $meal->no_rice,
                    'vegi' => $meal->vegi,
                ];
            })->toArray(),
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

                    DateTimePicker::make('delivery_date')
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
                        ->addActionLabel('Add Meal')
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

    public function save()
    {
        $data = $this->form->getState();
        
        try {
            \DB::beginTransaction();
            
            // Update the order
            $this->order->update([
                'customer_id' => $data['customer_id'],
                'address_id' => $data['address_id'],
                'delivery_date' => \Carbon\Carbon::parse($data['delivery_date'])
                    ->setTimeFromTimeString($data['arrival_time']),
                'notes' => $data['notes'],
                'driver_id' => $data['driver_id'],
                'driver_route' => $data['driver_route'],
                'backup_driver_id' => $data['backup_driver_id'] ?? null,
                'backup_driver_route' => $data['backup_driver_route'] ?? '',
            ]);

            // Update or create meals
            $this->order->meals()->delete(); // Remove existing meals
            foreach ($data['meals'] as $meal) {
                $this->order->meals()->create([
                    'meal_id' => $meal['meal_id'],
                    'normal_rice' => $meal['normal_rice'],
                    'small_rice' => $meal['small_rice'],
                    'no_rice' => $meal['no_rice'],
                    'vegi' => $meal['vegi'],
                ]);
            }
            
            \DB::commit();
            
            Notification::make()
                ->success()
                ->title('Order updated successfully')
                ->send();
                
            $this->redirect('/admin/orders');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error updating order')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();
        return [
            '/admin/drivers' => 'Drivers',
            '' => $record->name ?? 'Edit Driver',
        ];
    }

    protected function getRecord(): ?Order
    {
        return $this->order;
    }
}