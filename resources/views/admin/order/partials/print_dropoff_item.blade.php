<div class="driver_sheet">
    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr class="thead">
                <th colspan="2"><b>DRIVER:</b> {{ $deliveries[0]->driver->name }}</th>
                <th colspan="4"><b>DATE:</b> {{ format_date($deliveries[0]->delivery_date) }}</th>
            </tr>
            <tr class="thead">
                <th class="screen_80px print_40px">Drop Off</th>
                <th class="screen_80px print_40px">Arrival</th>
                <th class="screen_80px print_40px">Order No</th>
                <th>Company &amp Address</th>
                <th class="screen_120px print_80px">Name</th>
                <th class="screen_90px print_60px">HP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveries as $delivery) { ?>
                <tr>
                    <td class="text-nowrap">{{ $delivery->dropoff_time ? date('h:i A', strtotime($delivery->dropoff_time)) : '' }}</td>
                    <td class="text-nowrap">{{ date('h:i A', strtotime($delivery->arrival_time)) }}</td>
                    <td>{{ $delivery->order->formatted_id }}</td>
                    <td>{{ $delivery->address?->name }}, {{ $delivery->address->mall?->name ?: $delivery->address->area?->name }}</td>
                    <td>{{ $delivery->order->customer?->name }}</td>
                    <td>{{ $delivery->order->customer?->contact }}</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>