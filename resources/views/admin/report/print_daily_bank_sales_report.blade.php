@extends('admin.layouts.print')
@section('content')
<?php if (!$daily_sales_list) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 5; ?>
    <?php $page_ctr = 0; ?>
    <?php $current_page_items = 0; ?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
        <?php $grand_total = 0;?>
        <?php $HTML_grand_total = '';?>
        <?php foreach ($daily_sales_list as $date => $payments_method_list) { ?>
            <?php foreach ($payments_method_list as $payment_method => $orders_list) { ?>
                <?php 
                $group_size = count($orders_list);
                // Break if adding this group exceeds limit
                if ($current_page_items > 0 && ($current_page_items + $group_size) > $order_per_page) {
                    $current_page_items = 0;
                    $page_ctr++;
                    echo '</div><div class="print_pagebreak"></div>';
                    echo '<div id="div_Page-' . ($page_ctr + 1) . '" class="print_page_portrait">';
                }
                ?>
                <?php $payment_method_total_amount = 0;?>
                <?php $row_ctr = 0;?>
                <?php foreach($orders_list as $order){ ?>
                    <?php if ($current_page_items >= $order_per_page) { ?>
                        <?php $current_page_items = 0; ?>
                        <?php $page_ctr++; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="print_pagebreak"></div>
                        <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
                    <?php } ?>
                    <?php if ($current_page_items == 0 || $row_ctr == 0) { ?>
                        <div id="div_Payment-{{ $order->payment_method->id }}" class="print_payment">
                            <div class="heading">
                                <h3>@lang('Online Order Daily Sales Report')</h3>
                                <b>@lang ('Date'):</b> {{ date('d/m/Y', strtotime($order->created_at)) }}
                                <b style="margin-left:20px;">@lang('Payment'):</b> {{ $order->payment_method->name }}
                            </div>
                            <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr class="thead">
                                    <th class="screen_30px print_20px">@lang('No')</th>
                                    <th class="screen_90px print_90px">@lang('Order No')</th>
                                    <th>@lang('Company') &amp; @lang('Address')</th>
                                    <th class="screen_100px print_100px">@lang('Name')</th>
                                    <th class="screen_90px print_80px">@lang('HP')</th>
                                    <th class="screen_30px print_30px">@lang('Total') (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php } ?>
                    <tr>
                        <td>{{ $row_ctr + 1 }}</td>
                        <td>{{ $order->formattedId }}</td>
                        <td>{{ $order->address?->name }}. {{ $order->address->mall?->name ?: $order->address->area?->name.', '.$order->address->area?->postal }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $order->customer->contact }}</td>
                        <td>{{ $order->total_amount }}</td>
                    </tr>
                    <?php $payment_method_total_amount += $order->total_amount;?>
                    <?php $row_ctr++; ?>
                    <?php $current_page_items++; ?>
                <?php } ?>
                <?php $grand_total += $payment_method_total_amount; ?>
                    </tbody>
                </table>
            </div>

                <?php ob_start();?>
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($orders_list[0]->created_at)) }}</td>
                        <td>{{ $orders_list[0]->payment_method->name }}</td>
                        <td>{{ format_currency($payment_method_total_amount) }}</td>
                    </tr>
                <?php $HTML_grand_total .= ob_get_clean();?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="print_pagebreak"></div>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
        <div class="mb-2">
            <h3>@lang('Sales Online Order Grand Total')</h3>
        </div>
        <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr class="thead">
                <th>@lang('Date')</th>
                <th>@lang('Payment')</th>
                <th>@lang('Total')</th>
            </tr>
        </thead>
        <tbody>
            {!! $HTML_grand_total !!}
        </tbody>
        </table>
        <div class="total_payment mt-2 px-2 text-end">@lang('Grand Total'): {{ format_currency($grand_total) }}</div>
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
@page {
    size: A4 portrait;
}
body {
    font-family: 'Calibri';
}
div.print_payment {
    margin: 40px 0;
    width: 790px;
}
    .order_no {
        font-size: 1.6rem;
    }
    .order_date {
        line-height: 2rem;
    }
    .order_driver {
        font-size: 1.1rem;
        line-height: 2rem;
    }

    table.order_detail{
        border: 2px solid #000;
        width: 100%;
    }
        table.order_detail thead th {
            font-weight: 700;
            white-space: nowrap;
        }
        table.order_detail th,
        table.order_detail td {
            font-size: 0.9rem;
            border: 1px solid #666;
            padding: 3px 5px;
        }
    .total_payment {
        font-size: 1.2rem;
    }
</style>
@endsection