@extends('admin.layouts.print')
<?php if (!$orders_list) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 4; ?>
    <?php $page_ctr = 0; ?>
    <?php $page_item_ctr = 0; ?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
        <?php foreach ($orders_list as $order) { ?>
            <?php if ($page_item_ctr >= $order_per_page) { ?>
                <?php $page_item_ctr = 0; ?>
                <?php $page_ctr++; ?>
                </div>
                <div class="print_pagebreak"></div>
                <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page">
            <?php } ?>

            @include('admin.order.partials.print_data_order_item', ['order' => $order, 'page_item_ctr' => $page_item_ctr])

            <?php $page_item_ctr++; ?>
        <?php } ?>
    </div>
<?php } ?>
@section('style')
@parent
<style>
    @media screen {
        body {
            padding: 40px;
        }

        div.print_data {
            margin: 40px 0;
        }

        table.order_detail th,
        table.order_detail td {
            font-size: 0.9rem !important;
        }
    }

    @media print {
        div.print_data {
            padding-top: 40px;
            height: 25%;
        }
    }

    body {
        font-family: 'Calibri';
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

    table.order_detail {
        border: 2px solid #000;
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
</style>
@endsection