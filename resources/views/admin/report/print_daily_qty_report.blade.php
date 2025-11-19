@extends('admin.layouts.print')
@section('content')
<?php if (!$daily_qty_list) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 32;?>
    <?php $page_ctr = 0;?>
    <?php $page_item_ctr = 0;?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
        <div class="heading">
            <h3>@lang('Daily Quantity Report')</h3>
            <?php $first_item = !empty($daily_qty_list) ? reset($daily_qty_list) : null; ?>
            <b>@lang ('Date'):</b> {{ $first_item ? date('F Y', strtotime($first_item['date'])) : 'No Date' }}
        </div>
        
        <table class="order_detail" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr class="thead">
                <th class="screen_60px print_50px">@lang('Code')</th>
                <th>@lang('Meal')</th>
                <th class="screen_60px print_50px">Normal</th>
                <th class="screen_60px print_50px">Big</th>
                <th class="screen_60px print_50px">Small</th>
                <th class="screen_60px print_50px">N.Rice</th>
                <th class="screen_60px print_50px">Quantity</th>
            </tr>
        </thead>
        <tbody>
        <?php $row_ctr = 0;?>
        <?php foreach($daily_qty_list as $meal){; ?>
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
                            <th class="screen_60px print_50px">@lang('Code')</th>
                            <th>@lang('Meal')</th>
                            <th class="screen_60px print_50px">Normal</th>
                            <th class="screen_60px print_50px">Big</th>
                            <th class="screen_60px print_50px">Small</th>
                            <th class="screen_60px print_50px">N.Rice</th>
                            <th class="screen_60px print_50px">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php } ?>
            <tr>
                <?php $total = $meal['normal'] + $meal['big'] + $meal['small'] + $meal['no_rice']; ?>
                <td>{{ $meal['code'] }}</td>
                <td>{{ $meal['meal'] }}</td>
                <td>{{ $meal['normal'] }}</td>
                <td>{{ $meal['big'] }}</td>
                <td>{{ $meal['small'] }}</td>
                <td>{{ $meal['no_rice'] }}</td>
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