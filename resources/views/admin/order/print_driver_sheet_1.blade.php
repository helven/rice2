@extends('admin.layouts.print')
@section('content')
<?php if (!$orderList) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Driver Sheet, please select <b>Landscape</b>, A4 paper for best printing result</div>
    <?php $driver_per_page = 4; ?>
    <?php $driver_per_row = 2; ?>

    <?php $row_ctr = 0; ?>

    <?php $page_ctr = 0; ?>
    <?php $page_item_ctr = 0;?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_landscape">
        <?php foreach ($orderList as $driver => $orders) { ?>
            <?php $driver_id = str_replace('driver_', '', $driver); ?>
            <?php if($page_item_ctr >= $driver_per_page){ ?>
                <?php $page_item_ctr = 0;?>
                <?php $page_ctr++; ?>
                </div>
                <div class="print_pagebreak"></div>
                <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_landscape">
                <?php //$current_page++;?>
            <?php } ?>
            
            @include('admin.order.partials.print_driver_sheet_1_item', ['driver_id' => $driver_id, 'orders' => $orders, 'page_item_ctr' => $page_item_ctr])
                
            <?php $page_item_ctr++; ?>
        <?php } ?>
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
    @page {
        size: A4 landscape;
    }
    div.print_page_landscape {
        display: grid;
        gap: 10px;
        grid-template-columns: calc(50% - 20px) calc(50% - 20px);
        width: 1600px;
    }

    @media print {
        :root {
            font-size: 12px;
        }
    }

    div.driver_sheet {
        padding: 0;
        width: 100%;
    }
</style>
@endsection