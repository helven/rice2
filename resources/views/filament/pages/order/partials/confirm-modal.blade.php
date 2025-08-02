<div class="confirm-modal space-y-6">
    @php
        $formattedData = $this->getFormattedData();
    @endphp
    
    <!-- Order and Customer Information -->
    <div class="grid grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-lg mb-3 text-gray-900">Order Information</h3>
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Customer:</td>
                            <td class="py-2">{{ $formattedData['customer_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Address:</td>
                            <td class="py-2">{!! $formattedData['address'] !!}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Delivery Date:</td>
                            <td class="py-2">{{ $formattedData['delivery_date'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-lg mb-3 text-gray-900">Driver Information</h3>
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Arrival Time:</td>
                            <td class="py-2">{{ $formattedData['arrival_time'] }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Driver:</td>
                            <td class="py-2">{{ $formattedData['driver_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Route:</td>
                            <td class="py-2">{{ $formattedData['driver_route'] }}</td>
                        </tr>
                        @if(!empty($formattedData['backup_driver_name']))
                            <tr>
                                <td class="py-2 font-medium text-gray-700">Backup Driver:</td>
                                <td class="py-2">{{ $formattedData['backup_driver_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium text-gray-700">Backup Route:</td>
                                <td class="py-2">{{ $formattedData['backup_driver_route'] }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Driver Notes:</td>
                            <td class="py-2">{{ $formattedData['driver_notes'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column - Orders -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-3 text-gray-900">Orders</h3>
            <div class="space-y-4">
                @if(isset($formattedData['meals_by_date']))
                    {{-- CreateOrder structure --}}
                    @foreach($formattedData['meals_by_date'] as $date => $dayOrders)
                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                            <div class="font-medium text-gray-900 mb-3">
                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                            </div>
                            @include('filament.pages.order.partials.meal-table', [
                                'meals' => $dayOrders['meals'],
                                'total_amount' => $dayOrders['total_amount'],
                                'delivery_fee' => $dayOrders['delivery_fee'] ?? 0,
                                'notes' => $dayOrders['notes'] ?? ''
                            ])
                        </div>
                    @endforeach
                @else
                    {{-- EditOrder structure --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                        @include('filament.pages.order.partials.meal-table', [
                            'meals' => $formattedData['meals'],
                            'total_amount' => $formattedData['total_amount'],
                            'delivery_fee' => $formattedData['delivery_fee'] ?? 0,
                            'notes' => $formattedData['notes'] ?? ''
                        ])
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>