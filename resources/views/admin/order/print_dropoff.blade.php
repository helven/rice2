@extends('admin.layouts.print')
@section('content')
<?php if(count($deliveriesList) <= 0){ ?>
    <div class="print_page_title">No Order found.</div>
<?php }else{ ?>
    <div class="print_page_title">Driver Sheet, please select <b>Landscape</b>, A4 paper for best printing result</div>
    <?php $driverPerPage = 4;?>
    <?php $driverPerRow = 2;?>

    <?php $pageCtr = 0; ?>
    <?php $pageItemCtr = 0;?>
    <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_landscape">
        <?php foreach($deliveriesList as $date => $driverDeliveries){ ?>
            <?php foreach($driverDeliveries as $driver => $deliveries){ ?>
                <?php $driver_id = str_replace('driver_', '', $driver);?>
                <?php if($pageItemCtr >= $driverPerPage){ ?>
                    <?php $pageItemCtr = 0;?>
                    <?php $pageCtr++; ?>
                    </div>
                    <div class="print_pagebreak"></div>
                    <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_landscape">
                <?php } ?>
                
                @include('admin.order.partials.print_dropoff_item', ['driver_id' => $driver_id, 'deliveries' => $deliveries, 'pageItemCtr' => $pageItemCtr])

                <?php $pageItemCtr++;?>
            <?php } ?>
            <?php $pageItemCtr++;?>
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
    gap:10px;
    grid-template-columns: calc(50% - 10px) calc(50% - 10px);
}
    div.driver_sheet {
        padding: 0;
        width: 100%;
    }
</style>
@endsection