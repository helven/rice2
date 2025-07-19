<div class="confirm-modal space-y-4">
    @php
        $formattedData = $this->getFormattedData();
    @endphp
    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="font-semibold">Order Information:</div>
            <div>Customer: {{ $formattedData['customer_name'] }}</div>
            <div>Address:<br />{!! $formattedData['address'] !!}</div>
            <div>Delivery Date: {{ $formattedData['delivery_date'] }}</div>
        </div>

        <div>
            <div class="font-semibold">Orders:</div>
            <div class="space-y-2">
                @foreach($formattedData['meals_by_date'] as $date => $dayOrders)
                    <div class="border p-2 rounded">
                        <div class="font-medium mb-2">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
                        @foreach($dayOrders['meals'] as $meal)
                            <div class="grid grid-cols-6 gap-2 ml-4 mb-2">
                                <div>{{ $meal['name'] ?? $meal['meal_name'] ?? 'N/A' }}</div>
                                <div>N:{{ $meal['normal'] ?? 0 }}</div>
                                <div>B:{{ $meal['big'] ?? 0 }}</div>
                                <div>S:{{ $meal['small'] ?? 0 }}</div>
                                <div>SS:{{ $meal['s_small'] ?? 0 }}</div>
                                <div>NR:{{ $meal['no_rice'] ?? 0 }}</div>
                            </div>
                        @endforeach
                        <div class="text-sm text-gray-600 ml-4">
                            Amount: RM{{ number_format($dayOrders['total_amount'], 2) }}
                            @if(!empty($dayOrders['notes']))
                                <br>Notes: {{ $dayOrders['notes'] }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2">Total Amount: RM{{ number_format($formattedData['total_amount'], 2) }}</div>
        </div>

        <div>
            <div class="font-semibold">Driver Information:</div>
            <div>Arrival Time: {{ $formattedData['arrival_time'] }}</div>
            <div>Driver: {{ $formattedData['driver_name'] }}</div>
            <div>Route: {{ $formattedData['driver_route'] }}</div>
            @if(!empty($formattedData['backup_driver_name']))
                <div>Backup Driver: {{ $formattedData['backup_driver_name'] }}</div>
                <div>Backup Route: {{ $formattedData['backup_driver_route'] }}</div>
            @endif
            <div>Driver Notes:<br />{{ $formattedData['driver_notes'] }}</div>
        </div>
    </div>
</div>