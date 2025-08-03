@extends('admin.layouts.print')
@section('content')
<?php if (!$order) { ?>
    <div class="print_page_title">No Order found.</div>
<?php } else { ?>
    <div class="print_page_title">Order Sheet, please select <b>Portrait</b>, A4 paper for best printing result</div>
    <?php $pageCtr = 0; ?>
    <?php $rowCtr = 1; ?>
    <?php $pageItemCtr = 0; ?>
    <div id="div_Page-<?php echo ($pageCtr + 1); ?>" class="print_page_portrait" style="width: 700px;">
        <div class="invoice_payment_method">
            <h1 class="payment_method_name">{{ $order->payment_method->name }}</h1>
            {{ $order->payment_method->address_1 }}<br />
            {!! ($order->payment_method->address_2) ? $order->payment_method->address_2.'<br />' : '' !!}
            {{ $order->payment_method->postal_code }} {{ $order->payment_method->city }}
        </div>
        <div class="invoice_info_section flex justify-between items-center">
            <div class="billing_address">
                {{ $order->invoice->billing_name }}<br />
                {!! nl2br($order->invoice->billing_address) !!}
            </div>
            <div class="invoice_info">
                <h1>INVOICE</h1>
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
                        <td>{{ date(config('app.date_format'), strtotime($order->invoice->issue_date)) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div>
            <div class="order_info">
                <table>
                    <thead>
                        <tr>
                            <th class="col_no"></th>
                            <th class="col_name">Description</th>
                            <th class="col_qty screen_80px print_80px">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->meals as $meal) { ?>
                            <tr>
                                <td class="col_no">{{ $rowCtr }}.</td>
                                <td class="col_name">{{ $meal->meal->name }}</td>
                                <td class="col_qty">{{ $meal->total_qty }}</td>
                            </tr>
                            <?php $rowCtr++; ?>
                            <?php $pageItemCtr++; ?>
                        <?php } ?>
                        <tr>
                            <td class="total" colspan="3">Total: {{ ($order->total_amount + $order->total_delivery_fee) * (1 + $order->invoice->tax_rate) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="payment_terms">
                {!! nl2br($order->payment_method->payment_terms) !!}
            </div>
        </div>
        <div class="signature_section flex justify-between">
            <div class="received_by">Received By.</div>
            <div class="approved_by">Approved By.</div>
        </div>
    </div>
<?php } ?>
@endsection
@section('style')
@parent
<style>
    /* Header Section - Company Info */
    .invoice_payment_method {
        text-align: center;
        margin-bottom: 30px;
        line-height: 1.4;
    }


    .invoice_payment_method h1 {
        font-size: 2rem;
    }

    .invoice_info_section {
        margin-bottom: 30px;
    }

    /* Billing Address Section */
    .billing_address {
        padding: 15px;
        line-height: 1.4;
        flex-shrink: 0;
    }

    /* Invoice Info Section */
    .invoice_info h1 {
        font-size: 2.4rem;
    }

    .invoice_info table {
        margin-left: auto;
        border-collapse: collapse;
    }

    .invoice_info th {
        font-weight: 700;
        text-align: left;
        padding: 5px 10px 5px 0;
        white-space: nowrap;
        width: 80px;
    }

    .invoice_info td {
        text-align: left;
        padding: 5px 0;
        min-width: 120px;
        white-space: nowrap;
    }

    /* Order Info Table */
    .order_info {
        margin-bottom: 30px;
    }

    .order_info table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .order_info th {
        background-color: #f0f0f0;
        border: 1px solid #000;
        padding: 8px;
        font-weight: bold;
    }

    .order_info td {
        border: 1px solid #000;
        padding: 8px;
    }

    .order_info .col_no,
    .order_info .col_qty {
        text-align: center;
    }

    .order_info td.total {
        text-align: right;
    }

    .order_info tr:last-child td {
        font-weight: bold;
        background-color: #f0f0f0;
    }

    /* Payment Terms */
    .payment_terms {
        margin-bottom: 30px;
        line-height: 1.4;
    }

    /* Signature Section */
    .flex:last-child {
        margin-top: 40px;
        margin-bottom: 0;
    }


    /* Main Layout */
    .signature_section {
        display: flex;
        margin-bottom: 30px;
        gap: 20px;
    }

    .received_by,
    .approved_by {
        width: 45%;
        font-weight: bold;
        text-align: center;
    }

    @media print {
        body {
            font-size: 14px;
        }
    }
</style>
@endsection