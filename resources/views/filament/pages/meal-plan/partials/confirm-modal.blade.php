<div class="confirm-modal space-y-6">
    @php
        $formattedData = $this->getFormattedData();
    @endphp
    
    <div class="grid grid-cols-2 gap-6">
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
                            <td class="py-2 font-medium text-gray-700">Delivery Dates:</td>
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
                        @endif
                        <tr>
                            <td class="py-2 font-medium text-gray-700">Driver Notes:</td>
                            <td class="py-2">{{ $formattedData['driver_notes'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-3 text-gray-900">Meals (All Dates)</h3>
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                @include('filament.pages.order.partials.meal-table', [
                    'meals' => $formattedData['meals'],
                    'total_amount' => $formattedData['total_amount'],
                    'delivery_fee' => $formattedData['delivery_fee'],
                    'notes' => $formattedData['notes'] ?? ''
                ])
            </div>
        </div>
    </div>
</div>
