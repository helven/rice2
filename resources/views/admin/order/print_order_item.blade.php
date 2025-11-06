<div id="div_Order-{{ $order->id }}" class="print_data order-<?php echo ($pageItemCtr + 1); ?>">
    <div class="row">
        <div class="order_no">(Order No) <b style="margin-left:10px;">{{ $order->formattedId }}</b></div>
    </div>
    <div class="row">
        <div class="col-5 order_date"><b style="margin-right:10px;">DATE: </b>{{ format_date($order->delivery_date) }} {{ strtoupper(date('D', strtotime($order->delivery_date))) }}</div>
        <div class="col-7 order_driver"><b style="margin-right:10px;">Driver Name:</b> {{ $order->driver->name }}</div>
    </div>

    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th class="text-end" style="width:30px;"></th>
                <th style="width:120px;">Company &amp Address: </th>
                <th colspan="3" style="width:370px;"><span style="font-weight:300;">{{ $order->address->name }}</span></th>
                <th style="width:80px;">Quantity</th>
                <th style="width:120px;"></th>
            </tr>
        <tbody>
            <tr>
                <td class="text-end">1</td>
                <td>(聯絡人名字)</td>
                <td style="max-width:90px;width:90px;">Name</td>
                <td style="max-width:220px;width:120px;">{{ $order->customer->name }}</td>
                <td rowspan="3" style="vertical-align:middle;white-space:pre-wrap;min-width:230px;"><b style="font-size:1.1rem;"></b></td>
                <td></td>
                <td rowspan="4" class="text-center" style="min-width:60px;width:60px;">{{ $order->notes }}</td>
            </tr>
            <tr>
                <td class="text-end">2</td>
                <td>(聯絡人電話)</td>
                <td>Phone No</td>
                <td>{{ $order->customer->contact }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-end">3</td>
                <td>(到餐時間)</td>
                <td>Arrival time</td>
                <td>{{ date('H:iA', strtotime($order->arrival_time)) }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-end">4</td>
                <td>(付款方式)</td>
                <td>Payment</td>
                <td>{{ $order->payment_method }}</td>
                <td></td>
                <td class="text-end"><b style="font-size:1.1rem;"></b></td>
            </tr>
            <td>&nbsp;</td>
            <td colspan="5"></td>
            <tr>

            </tr>
        </tbody>
    </table>
</div>