<div id="div_Order-{{ $delivery->order->id }}" class="print_data order-<?php echo ($pageItemCtr + 1); ?>">
    <div class="row">
        <div class="order_no">(Order No) <b style="margin-left:10px;">{{ $delivery->order->order_no }}</b></div>
    </div>
    <div style="display:flex;justify-content:space-between;">
        <div class="order_date"><b style="margin-right:10px;">DATE: </b>{{ format_date($delivery->delivery_date) }} {{ strtoupper(date('D', strtotime($delivery->delivery_date))) }}</div>
        <div class="order_driver"><b style="margin-right:10px;">Driver Name:</b> {{ $delivery->driver?->name }}</div>
        <div class="arrival_time">{{ date('H:iA', strtotime($delivery->arrival_time)) }}</div>
    </div>

    <?php $mealCount = count($delivery->order->meals);?>
    <?php $rows = $mealCount <= 4 ? 4 : $mealCount;?>
    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2" class=""><b>{{ $delivery->address?->name }}</b></td>
            <td colspan="6"><b>{{ ($delivery->address->address_1?$delivery->address->address_1.', ':'') }} {{ $delivery->address->mall?->name ?: $delivery->address->area?->name.', '.$delivery->address->area?->postal }}</b></td>
        </tr>

        <tr>
            <td colspan="2" rowspan="{{ $rows - 1 }}">{{ $delivery->order->notes }}</td>
            <th></th>
            <th class="screen_50px print_50px text-center">Normal</th>
            <th class="screen_50px print_50px text-center">Big</th>
            <th class="screen_50px print_50px text-center">Small</th>
            <th class="screen_50px print_50px text-center">No Rice</th>
            <th class="screen_50px print_50px text-center">Total</th>
        </tr>

        <?php $total_qty = 0; ?>
        <?php foreach($delivery->order->meals as $meal){
            $total_qty += $meal->normal + $meal->big + $meal->small + $meal->no_rice;
        }?>

        <?php $ctrMeal = 0;?>
        <?php for($i = 0; $i < $rows; $i++) { ?>
            <tr>
                <?php if($i == $rows - 2){ ?>
                    <td class="screen_120px print_120px">{{ $delivery->order->customer->name }}</td>
                    <td class="screen_120px print_120px">{{ $delivery->order->customer->contact }}</td>
                <?php }elseif($i == $rows - 1){ ?>
                    <td class="screen_120px print_120px">{{ $delivery->order->payment_method->label }} : {{ $delivery->order->payment_status->label }}</td>
                    <td class="screen_120px print_120px">Qty : {{ $total_qty }}</td>
                <?php } ?>

                <?php if($i < $mealCount) { ?>
                    <?php $meal = $delivery->order->meals[$i];?>
                    <td>{{ $meal->meal->name }}</td>
                    <td class="text-center">{{ $meal->normal }}</td>
                    <td class="text-center">{{ $meal->big }}</td>
                    <td class="text-center">{{ $meal->small }}</td>
                    <td class="text-center">{{ $meal->no_rice }}</td>
                    <td class="text-center">{{ $meal->normal + $meal->big + $meal->small + $meal->no_rice }}</td>
                <?php }else{ ?>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                <?php } ?>
            </tr>
            <?php $ctrMeal++; ?>
        <?php } ?>
    </table>
</div>