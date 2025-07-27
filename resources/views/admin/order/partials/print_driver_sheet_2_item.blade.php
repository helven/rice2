<div class="driver_sheet_container">
    <div class="driver_sheet">
        <div class="driver_sheet_data">
            <div class="order_no">{{ $order->formatted_id }}</div>
            <div class="destination">{{ $order->address?->name }}. {{ $order->address->mall?->name ?: $order->address->area?->name }}</div>
            <div class="name">{{ $order->customer?->name }}</div>
        </div>
        <div class="driver_name">{{ $order->driver->name }}</div>
    </div>
</div>