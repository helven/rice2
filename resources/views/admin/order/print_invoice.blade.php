@extends('admin.layouts.print')
@section('content')
<?php if (!$order) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $order_per_page = 4; ?>
    <?php $page_ctr = 0; ?>
    <?php $page_item_ctr = 0; ?>
    <div id="div_Page-<?php echo ($page_ctr + 1); ?>" class="print_page_portrait">
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
                    <tr>
                        <th>Order No.</th>
                        <td>{{ $order->order_no }}</td>
                    </tr>
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