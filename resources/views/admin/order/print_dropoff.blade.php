@extends('admin.layouts.print')
<?php if(count($orders_list) <= 0){ ?>
    <div class="print_page_title">No Order found.</div>
<?php }else{ ?>
    <div class="print_page_title">Driver Sheet, please select <b>Landscape</b>, A4 paper for best printing result</div>
    <?php $driver_per_page = 4;?>
    <?php $driver_per_row = 2;?>
    <?php //$total_page = (int)(ceil($total_sheets / $driver_per_page));?>
    <?php //$current_page = 1;?>
    <?php $row_ctr = 0;?>
    <?php $page_item_ctr = 0;?>
    <?php foreach($orders_list as $driver => $orders){ ?>
        <?php $driver_id = str_replace('driver_', '', $driver);?>
        <?php $page_row_class = "even";?>
        <?php if($page_item_ctr == 0){ ?>
            <?php $page_row_class = "odd";?>
        <?php } ?>
        <?php if($page_item_ctr >= $driver_per_page){ ?>
            <?php $page_item_ctr = 0;?>
            <?php $page_row_class = "odd";?>
            <div class="print_pagebreak"></div>
            <?php //$current_page++;?>
        <?php } ?>
        <?php if($row_ctr == 0){ ?><div class="page_row {{ $page_row_class }}"><?php } ?>
        <div class="driver_sheet_container">
            <div id="div_Order-{{ $driver_id }}" class="col-6 driver_sheet">
                <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
                <thead>
                    <tr class="thead">
                        <th colspan="2"><b>DRIVER:</b> {{ $orders[0]->driver_name }}</th>
                        <th colspan="4"><b>DATE:</b> {{ format_date($orders[0]->delivery_date) }}</th>
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
                <?php foreach($orders as $order){ ?>
                    <tr>
                        <td class="text-nowrap">{{ $order->dropoff_time ? date('h:i A', strtotime($order->dropoff_time)) : '' }}</td>
                        <td class="text-nowrap">{{ date('h:i A', strtotime($order->arrival_time)) }}</td>
                        <td>{{ $order->formatted_id }}</td>
                        <td>{{ $order->address_name }}, {{ $order->mall_name ?: $order->area_name }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->customer_contact }}</td>
                    </tr>
                <?php } ?>
                <?php //$filler_rows = $highest_rows - count($order);?>
                <?php /*if($filler_rows > 0){ ?>
                    <?php for($i = 0; $i < $filler_rows; $i++){ ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php } ?>
                <?php }*/ ?>
                </tbody>
                </table>
            </div>
        </div>
        <?php $row_ctr++;?>
        <?php if($row_ctr >= $driver_per_row){ ?><?php $row_ctr = 0;?><div class="clear"></div></div> <?php } ?>
        <?php $page_item_ctr++;?>
    <?php } ?>
<?php } ?>
@section('style')
@parent
<style>
div.driver_sheet_container {
    margin-right: 10px;
}
    div.driver_sheet {
        padding: 0;
        width: 100%;
    }
</style>
@endsection