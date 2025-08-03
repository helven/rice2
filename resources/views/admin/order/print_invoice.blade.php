@extends('admin.layouts.print')
@section('content')
<?php if (!$order) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $pageCtr = 0; ?>
    <?php $rowCtr = 1; ?>
    <?php $pageItemCtr = 0; ?>
    <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_portrait">
        <div class="invoice_payment_method">
            {{ $order->payment_method->name }}<br />
            {{ $order->payment_method->address_1 }}<br />
            {!! ($order->payment_method->address_2) ? $order->payment_method->address_2.'<br />' : '' !!}
            {{ $order->payment_method->postal_code }} {{ $order->payment_method->city }}
        </div>
        <div class="flex">
            <div class="billing_address">
                {{ $order->invoice->billing_name }}
                {{ $order->invoice->billing_address }}
            </div>
            <div class="invoice_info">
                <table>
                    <tr>
                        <th>Invoice No.</th>
                        <td>{{ $order->invoice->invoice_no }}</td>
                    </tr>
                    <tr>
                        <th>Your Ref.</th>
                        <td>{{ $order->invoice->ref_no }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ date(config('app.dateformat'), strtotime($order->invoice->issued_at)) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div>
            <div class="order_info">
                <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Description</th>
                        <th class="screen_80px print_80px">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($order->meals as $meal){ ?>
                        <tr>
                            <th>{{ $rowCtr }}.</th>
                            <td>{{ $meal->meal->name }}</td>
                            <td>{{ $meal->total_qty }}</td>
                        </tr>
                        <?php $rowCtr++; ?>
                        <?php $pageItemCtr++; ?>
                    <?php } ?>
                </tbody>
                </table>
            </div>
            <div class="payment_terms">
                {!! nl2br($order->payment_method->payment_terms) !!}
            </div>
        </div>  
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
    @media screen {
        
    }

    @media print {
     
    }
</style>
@endsection