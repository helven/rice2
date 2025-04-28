<?php

namespace App\Filament\Pages\Order;

use App\Filament\Pages\AbstractFilamentPage;
use App\Models\CustomerAddressBook;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

use App\Models\Customer;

class CreateOrder extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static ?string $navigationLabel = 'New Order';
    protected static ?string $title = 'New Order';
    protected static ?string $slug = 'orders/create';
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.order.create-order';

    public $customer_id = null;
    public $address_id = null;
    public $delivery_date = null;

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
                                    \Log::info('Customer ID selected:', ['customer_id' => $state]);
                                }),

                            Select::make('address_id')
                                ->label('Delivery Location')
                                ->placeholder('Select Address')
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
                                        ->where('status', 1)
                                        ->get()
                                        ->mapWithKeys(function ($address) {
                                            $address->address_1 = trim($address->address_1);
                                            $address->address_2 = trim($address->address_2);
                                            ob_start();?>
<div>
    <span class="font-bold"><?php echo $address->name;?></span>
    <?php echo ($address->is_default)?"<span class='text-xs italic'>(default)":""."<span>";?>
</div>
<div class='text-sm'><?php echo $address->address_1;?></div>
                                            <?php
                                            $displayAddress = ob_get_clean();
                                            return [$address->id => $displayAddress];
                                        });
                                })
                        ]),
                    DatePicker::make('delivery_date')
                        ->label('Delivery Date')
                        ->placeholder('Delivery Date')
                        ->required()
                        //->hourMode(12)
                        ->minDate(now())
                        ->timezone('Asia/Kuala_Lumpur')
                        ->displayFormat('Y/m/d')
            ]),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}