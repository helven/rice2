@extends('admin.layouts.print')
@section('content')
<?php if (count($orders_list) <= 0) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Driver Sheet, please select <b>Landscape</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 4;?>
    <?php $order_per_row = 2;?>

    <?php $page_ctr = 0; ?>
    <?php $page_item_ctr = 0;?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_landscape">
        <?php foreach ($orders_list as $order) { ?>
            <?php for($i = 1; $i <= ($order->total_qty / 6); $i++){ // each piece can be used for 6 meal only ?>
                <?php if($page_item_ctr >= $order_per_page){ ?>
                    <?php $page_item_ctr = 0;?>
                    <?php $page_ctr++; ?>
                    </div>
                    <div class="print_pagebreak"></div>
                    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_landscape">
                <?php } ?>
                
                @include('admin.order.partials.print_driver_sheet_2_item', ['order' => $order, 'page_item_ctr' => $page_item_ctr])

                <?php $page_item_ctr++;?>
            <?php } ?>
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
    body {
        font-family: 'Calibri';
    }
        div.print_page_landscape {
            display: grid;
            gap: 30px;
            grid-template-columns: calc(50% - 30px) calc(50% - 30px);
            /*width: 1464px;*/
            width: 964px;
        }
        div.driver_sheet_container {
            height: 300px;
            width: 450px;
        }
            div.driver_sheet {
                border: 1px solid #000;
                height: 100%;
                overflow: hidden;
                position: relative;
                widtH: 100%;
            }
                div.driver_sheet div.order_no,
                div.driver_sheet div.destination,
                div.driver_sheet div.name {
                    line-height: 1.5em;
                    text-align: center;
                }
                div.driver_sheet div.order_no {
                    font-size: 3.25rem;
                }
                div.driver_sheet div.destination {
                    font-size: 1.15em;
                }
                div.driver_sheet div.name {
                    font-size: 1.4rem;
                }
                div.driver_sheet div.driver_name {
                    font-size: 1rem;
                }
                div.driver_sheet > div.driver_sheet_data {
                    display: flex;
                    flex-direction: column;
                    font-weight: 700;
                    height: 100%;
                    justify-content: center;
                    margin: 0 20px;
                    width: calc(100% - 40px);
                }
                div.driver_sheet div.driver_name {
                    bottom: 5px;
                    position: absolute;
                    right: 10px;
                }
    @media screen {
        body {
            padding: 40px;
        }
    }
    @media print {
        div.print_page_landscape {
            grid-template-columns: calc(50vw - 30px) calc(50vh - 30px);
        }
        div.driver_sheet_container {
            height: calc(50vh - 20px);
            width: calc(50vw - 20px);
        }
        div.driver_sheet div.order_no,
        div.driver_sheet div.destination,
        div.driver_sheet div.name {
            line-height: 1.5em;
        }
        div.driver_sheet div.order_no {
            font-size: 5.4rem;
        }
        div.driver_sheet div.destination {
            font-size: 1.92rem;
        }
        div.driver_sheet div.name {
            font-size: 2.3rem;
        }
        div.driver_sheet div.driver_name {
            font-size: 1.2rem;
        }
    }
</style>
@endsection