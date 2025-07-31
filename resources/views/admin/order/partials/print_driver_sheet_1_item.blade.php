
<div class="driver_sheet">
    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr class="thead">
                <th colspan="2"><b>DRIVER:</b> {{ $orders[0]->driver->name }}</th>
                <th colspan="4"><b>DATE:</b> {{ format_date($orders[0]->delivery_date) }}</th>
            </tr>
            <tr class="thead">
                <th class="screen_60px print_60px">Arrival</th>
                <th class="screen_90px print_80px">Order No</th>
                <th>Company &amp Address</th>
                <th class="screen_110px print_100px">Name</th>
                <th class="screen_90px print_80px">HP</th>
                <th class="screen_20px print_20px">QTY</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order) { ?>
                <tr>
                    <td class="text-nowrap">{{ date('h:i A', strtotime($order->arrival_time)) }}</td>
                    <td>{{ $order->formatted_id }}</td>
                    <td>{{ $order->address?->name }}. {{ $order->address->mall?->name ?: $order->address->area?->name }}</td>
                    <td>{{ $order->customer?->name }}</td>
                    <td>{{ $order->customer?->contact }}</td>
                    <td>{{ $order->total_qty }}</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
