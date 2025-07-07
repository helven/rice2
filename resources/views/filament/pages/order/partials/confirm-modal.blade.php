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
            <div class="font-semibold">Meal Information:</div>
            <div class="space-y-2">
                @foreach($formattedData['meals'] as $meal)
                    <div class="border p-2 rounded">
                        <div class="grid grid-cols-6 gap-2">
                            <div>Meal: {{ $meal['name'] ?? $meal['meal_name'] ?? 'N/A' }}</div>
                            <div>Normal: {{ $meal['normal'] ?? $meal['normal_rice'] ?? 0 }}</div>
                            <div>Big: {{ $meal['big'] ?? 0 }}</div>
                            <div>Small: {{ $meal['small'] ?? $meal['small_rice'] ?? 0 }}</div>
                            <div>S.Small: {{ $meal['s_small'] ?? 0 }}</div>
                            <div>No Rice: {{ $meal['no_rice'] ?? 0 }}</div>
                            <div>Qty: {{ $meal['qty'] ?? 0 }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div>Total Amount: {{ $formattedData['total_amount'] }}</div>
            <div>Notes:<br />{{ $formattedData['notes'] }}</div>
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