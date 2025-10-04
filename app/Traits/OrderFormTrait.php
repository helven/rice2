<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\CustomerAddressBook;
use App\Models\Driver;
use App\Models\Area;
use App\Models\Meal;
use App\Models\AttrPaymentMethod;
use App\Models\OrderStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;

trait OrderFormTrait
{
    private function getDefaultPaymentStatusId(): int
    {
        return 3;
    }

    private function createMealQuantityField(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->default(0)
            ->minValue(0)
            ->maxValue(1000)
            ->live()
            ->afterStateUpdated(fn($state, $set, $get) => $this->calculateTotalAmountByMealQty($set, $get))
            ->rules(['required', 'integer', 'min:0', 'max:1000']);
    }

    protected function getCustomerAddressGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('customer_id')
                ->label('Customer Name')
                ->placeholder('Select Customer')
                ->required()
                ->searchable()
                ->preload()
                ->getSearchResultsUsing(function (string $search) {
                    $search = trim($search);
                    if (empty($search) || strlen($search) > 100) {
                        return [];
                    }
                    
                    $escapedSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                    
                    return Customer::query()
                        ->where(function ($q) use ($escapedSearch) {
                            $q->where('name', 'like', "%{$escapedSearch}%")
                              ->orWhere('contact', 'like', "%{$escapedSearch}%");
                        })
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->options(Customer::query()->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $set('address_id', null);
                    
                    if ($state) {
                        $customer = Customer::find($state);
                        if ($customer && $customer->payment_method_id) {
                            $set('payment_method_id', $customer->payment_method_id);
                        }
                    }
                    
                    // Call the specific handler if it exists
                    if (method_exists($this, 'onCustomerChanged')) {
                        $this->onCustomerChanged($state, $set, $get);
                    }
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
                            <div class="hidden"><?php echo e($address->name) . '|' . e($address->city); ?></div>
                            <span class="font-bold"><?php echo e($address->name); ?></span>
                            <?php echo $address->is_default ? '<span class="italic text-xs text-gray-400"> (default)</span>' : ""; ?>
                            <div><?php echo e($address->address_1); ?><br />
                                <?php echo ($address->address_2) ? e($address->address_2) . '<br />' : ""; ?>
                                <?php echo e($address->postcode); ?> <?php echo e($address->city); ?>
                            </div>
                            <?php
                            $displayAddress = trim(ob_get_clean());
                            return [$address->id => $displayAddress];
                        });
                })
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state) {
                        $address = CustomerAddressBook::find($state);
                        if ($address) {
                            if ($address->driver_id) {
                                $set('driver_id', $address->driver_id);
                                if ($address->driver_route) {
                                    $set('driver_route', $address->driver_route);
                                }
                            }

                            if ($address->backup_driver_id) {
                                $set('backup_driver_id', $address->backup_driver_id);
                            }
                        }
                    }
                    
                    // Call the specific handler if it exists
                    if (method_exists($this, 'onAddressChanged')) {
                        $this->onAddressChanged($state, $set, $get);
                    }
                })
        ]);
    }

    protected function getPaymentGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('payment_status_id')
                ->label('Payment Status')
                ->placeholder('Select Payment Status')
                ->required()
                ->searchable()
                ->preload()
                ->options(OrderStatus::query()->pluck('label', 'id'))
                ->default($this->getDefaultPaymentStatusId())
                ->live(),

            Select::make('payment_method_id')
                ->label('Payment Method')
                ->placeholder('Select Payment Method')
                ->required()
                ->searchable()
                ->preload()
                ->options(AttrPaymentMethod::query()->pluck('label', 'id'))
                ->live(),
        ]);
    }

    protected function getDriverGrid(): Grid
    {
        return Grid::make(2)->schema([
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

                    $driver = Driver::find($driverId);
                    if (!$driver || !$driver->route) {
                        return [];
                    }
                    return collect($driver->route)->pluck('route_name', 'route_name');
                })
                ->disabled(fn(callable $get): bool => blank($get('driver_id')))
        ]);
    }

    protected function getBackupDriverGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('backup_driver_id')
                ->label('Backup Driver')
                ->placeholder('Select Backup Driver')
                ->searchable()
                ->preload()
                ->options(Driver::query()->pluck('name', 'id'))
                ->live()
        ]);
    }

    protected function getMealSelect(): Select
    {
        return Select::make('meal_id')
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
            ->columnSpan(2);
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

        usort($deliveryFeeRules, function ($a, $b) {
            return $b['qty'] - $a['qty'];
        });

        foreach ($deliveryFeeRules as $rule) {
            if ($totalQty >= $rule['qty']) {
                return $rule['delivery_fee'];
            }
        }

        return end($deliveryFeeRules)['delivery_fee'] ?? 0;
    }


}
