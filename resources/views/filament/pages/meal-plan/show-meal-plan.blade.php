<x-filament::page>
    <div class="space-y-6">
        {{-- Order Information --}}
        <x-filament::section>
            <x-slot name="heading">Order Information</x-slot>
            <div class="space-y-2">
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Order No:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getOrderData()['order_no'] }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getOrderData()['customer_name'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Location:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getOrderData()['delivery_location'] }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getOrderData()['payment_status'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getOrderData()['payment_method'] }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date:</span>
                        <div class="inline-flex flex-wrap gap-1 ml-2">
                            @foreach($this->getOrderData()['delivery_date'] as $date)
                            <span style="--c-50:var(--gray-50);--c-400:var(--gray-400);--c-600:var(--gray-600);" class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-gray">
                                <span class="grid"><span class="truncate">{{ $date }}</span></span>
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Meals --}}
        <x-filament::section>
            <x-slot name="heading">Meals</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Meal Name</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Normal</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Big</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Small</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">No Rice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getMealsData() as $meal)
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $meal['meal_name'] }}</td>
                            <td class="py-2 px-3 text-center text-gray-900 dark:text-white">{{ $meal['normal'] }}</td>
                            <td class="py-2 px-3 text-center text-gray-900 dark:text-white">{{ $meal['big'] }}</td>
                            <td class="py-2 px-3 text-center text-gray-900 dark:text-white">{{ $meal['small'] }}</td>
                            <td class="py-2 px-3 text-center text-gray-900 dark:text-white">{{ $meal['no_rice'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($order->notes)
            <div class="mt-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes:</span>
                <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $order->notes }}</p>
            </div>
            @endif
        </x-filament::section>

        {{-- Driver Information --}}
        <x-filament::section>
            <x-slot name="heading">Driver Information</x-slot>
            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Arrival Time:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getDriverData()['arrival_time'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">DropOff Time:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getDriverData()['dropoff_time'] }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Driver:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getDriverData()['driver'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Route:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getDriverData()['route'] }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Backup Driver:</span>
                        <span class="text-sm text-gray-900 dark:text-white ml-2">{{ $this->getDriverData()['backup_driver'] }}</span>
                    </div>
                </div>
                @if($this->getDriverData()['notes'])
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes:</span>
                        <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $this->getDriverData()['notes'] }}</p>
                    </div>
                </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Invoice Information --}}
        <x-filament::section>
            <x-slot name="heading">Invoice Information</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Issue Date</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Invoice No</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Subtotal</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Delivery Fee</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Tax Rate</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getInvoicesData() as $invoice)
                        <tr class="border-b dark:border-gray-700 {{ $invoice['is_active'] ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                            <td class="py-2 px-3 {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['issue_date'] }}</td>
                            <td class="py-2 px-3">
                                <div class="flex gap-1.5 flex-wrap">
                                    <div class="flex w-max">
                                        <span style="--c-50:var(--{{ $invoice['is_active'] ? 'success' : 'gray' }}-50);--c-400:var(--{{ $invoice['is_active'] ? 'success' : 'gray' }}-400);--c-600:var(--{{ $invoice['is_active'] ? 'success' : 'gray' }}-600);" class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-{{ $invoice['is_active'] ? 'success' : 'gray' }}">
                                            <span class="grid"><span class="truncate">{{ $invoice['status'] }}</span></span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-3 {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['invoice_no'] }}</td>
                            <td class="py-2 px-3 text-right {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['subtotal'] }}</td>
                            <td class="py-2 px-3 text-right {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['delivery_fee'] }}</td>
                            <td class="py-2 px-3 text-right {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['tax_rate'] }}%</td>
                            <td class="py-2 px-3 text-right {{ $invoice['is_active'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $invoice['total_amount'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Delivery Information --}}
        <x-filament::section>
            <x-slot name="heading">Delivery Information</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Delivery No</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Delivery Date</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Arrival Time</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">DropOff Time</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getDeliveriesData() as $delivery)
                        <tr class="border-b dark:border-gray-700 {{ $delivery['is_today'] ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}">
                            <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $delivery['delivery_no'] }}</td>
                            <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $delivery['delivery_date'] }}</td>
                            <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $delivery['arrival_time'] }}</td>
                            <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $delivery['dropoff_time'] }}</td>
                            <td class="py-2 px-3">
                                <div class="flex gap-1.5 flex-wrap">
                                    <div class="flex w-max">
                                        @php
                                            $color = $delivery['status_id'] == 1 ? 'warning' : ($delivery['status_id'] == 2 ? 'success' : 'gray');
                                        @endphp
                                        <span style="--c-50:var(--{{ $color }}-50);--c-400:var(--{{ $color }}-400);--c-600:var(--{{ $color }}-600);" class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-{{ $color }}">
                                            <span class="grid"><span class="truncate">{{ $delivery['status'] }}</span></span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Actions --}}
        <div class="flex justify-center gap-3">
            <x-filament::button tag="a" href="/backend/orders" color="gray">
                Back to Orders
            </x-filament::button>
        </div>
    </div>
</x-filament::page>
