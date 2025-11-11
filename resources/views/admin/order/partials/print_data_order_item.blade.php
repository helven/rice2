<div id="div_Order-{{ $delivery->order->id }}" class="print_data order-<?php echo ($pageItemCtr + 1); ?>">
    <div class="row">
        <div class="order_no">(Order No) <b style="margin-left:10px;">{{ $delivery->order->order_no }}</b></div>
    </div>
    <div class="row">
        <div class="col-5 order_date"><b style="margin-right:10px;">DATE: </b>{{ format_date($delivery->delivery_date) }} {{ strtoupper(date('D', strtotime($delivery->delivery_date))) }}</div>
        <div class="col-7 order_driver"><b style="margin-right:10px;">Driver Name:</b> {{ $delivery->driver?->name }}</div>
    </div>

    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th class="text-end" style="width:30px;"></th>
                <th style="width:120px;">Company &amp Address: </th>
                <th colspan="3" style="width:370px;"><span style="font-weight:300;">{{ $delivery->address?->name }}. {{ $delivery->address->mall?->name ?: $delivery->address->area?->name.', '.$delivery->address->area?->postal }}</span></th>
                <th style="width:80px;">Quantity</th>
                <th style="width:120px;"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-end">1</td>
                <td>(聯絡人名字)</td>
                <td style="max-width:90px;width:90px;">Name</td>
                <td style="max-width:220px;width:120px;">{{ $delivery->order->customer->name }}</td>
                <?php ob_start();
                    foreach ($delivery->order->meals as $orderMeal) {
                        ?><b style="font-size:1.1rem;">{{ $orderMeal->meal->name }}</b><br /><?php
                    }
                    $meals = trim(ob_get_clean());
                ?>
                <td rowspan="3" style="vertical-align:middle;white-space:pre-wrap;min-width:230px;">{!! $meals !!}</td>
                <td></td>
                <td rowspan="4" class="text-center" style="min-width:60px;width:60px;">{{ $delivery->order->notes }}</td>
            </tr>
            <tr>
                <td class="text-end">2</td>
                <td>(聯絡人電話)</td>
                <td>Phone No</td>
                <td>{{ $delivery->order->customer->contact }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-end">3</td>
                <td>(到餐時間)</td>
                <td>Arrival time</td>
                <td>{{ date('H:iA', strtotime($delivery->arrival_time)) }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-end">4</td>
                <td>(付款方式)</td>
                <td>Payment</td>
                <td>{{ $delivery->order->payment_method->label }}</td>
                <td></td>
                <td class="text-end"><b style="font-size:1.1rem;">{{ $delivery->order->total_qty }}</b></td>
            </tr>
            <td>&nbsp;</td>
            <td colspan="5"></td>
            <tr>

            </tr>
        </tbody>
    </table>
</div>