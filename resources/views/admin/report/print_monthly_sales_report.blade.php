@extends('admin.layouts.print')
@section('content')
<?php if (!$monthly_sales_list) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 32;?>
    <?php $page_ctr = 0;?>
    <?php $page_item_ctr = 0;?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
        <div class="heading">
            <h3>@lang('Online Order Monthly Sales Report')</h3>
            <?php $first_item = !empty($monthly_sales_list) ? reset($monthly_sales_list) : null; ?>
            <b>@lang ('Date'):</b> {{ $first_item ? date('F Y', strtotime($first_item['date'])) : 'No Date' }}
        </div>
        
        <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr class="thead">
                <th class="screen_60px print_50px">@lang('Date')</th>
                <?php foreach($payment_methods as $payment_method) {?>
                    <th class="screen_60px print_50px">{{ strtoupper($payment_method) }}</th>
                <?php } ?>
                <th class="screen_60px print_50px">Total (RM)</th>
            </tr>
        </thead>
        <tbody>
        <?php $row_ctr = 0;?>
        <?php foreach($monthly_sales_list as $sales){ ?>
            <?php if ($page_item_ctr >= $order_per_page) { ?>
                <?php $page_item_ctr = 0; ?>
                <?php $page_ctr++; ?>
                </tbody>
                </table>
                </div>
                <div class="print_pagebreak"></div>
                <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
                    <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr class="thead">
                            <th class="screen_60px print_50px">@lang('Date')</th>
                            <?php foreach($payment_methods as $payment_method) {?>
                                <th class="screen_60px print_50px">{{ strtoupper($payment_method) }}</th>
                            <?php } ?>
                            <th class="screen_60px print_50px">Total (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        
            <?php } ?>
            <tr>
                <td>{{ $sales['date'] }}</td>
                <?php $total = 0;?>
                <?php foreach($payment_methods as $payment_method) {?>
                    <td>{{ $sales['payment_'.$payment_method] }}</td>
                    <?php $total += $sales['payment_'.$payment_method];?>
                <?php } ?>
                <td>{{ $total }}</td>
            </tr>
            <?php $row_ctr++; ?>
            <?php $page_item_ctr++; ?>
        <?php } ?>
        </tbody>
        </table>
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
@page {
    size: A4 portrait;
}
    .total_payment {
        font-size: 1.2rem;
    }
</style>
@endsection