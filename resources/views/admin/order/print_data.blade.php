@extends('admin.layouts.print')
@section('content')
<?php if (!$deliveriesList) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $orderPerPage = 4; ?>
    <?php $pageCtr = 0; ?>
    <?php $pageItemCtr = 0; ?>
    <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_portrait">
        <?php foreach ($deliveriesList  as $delivery) { ?>
            <?php if ($pageItemCtr >= $orderPerPage) { ?>
                <?php $pageItemCtr = 0; ?>
                <?php $pageCtr++; ?>
                </div>
                <div class="print_pagebreak"></div>
                <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_portrait">
            <?php } ?>

            @include('admin.order.partials.print_data_order_item', ['delivery' => $delivery, 'pageItemCtr' => $pageItemCtr])

            <?php $pageItemCtr++; ?>
        <?php } ?>
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
    @media screen {
        div.print_data {
            margin: 40px 0;
        }

        table.order_detail th,
        table.order_detail td {
            font-size: 0.9rem !important;
        }
        table.order_detail th {
            font-weight: 700;
        }
    }

    @media print {
        div.print_data {
            padding-top: 40px;
            height: 25vh
        }

        table.order_detail th,
        table.order_detail td {
            font-size: 1.1rem !important;
        }
    }

    body {
        font-family: 'Calibri';
    }

    .order_no {
        font-size: 1.6rem;
    }

    .order_date,
    .order_driver,
    .arrival_time {
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
    .text-center {
        text-align: center;
    }
</style>
@endsection