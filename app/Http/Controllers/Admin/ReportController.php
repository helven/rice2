<?php

namespace App\Http\Controllers\Admin;

use DB;
use File;
use App\Http\Controllers\AdminController;
use App\Models\Order;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use App\Imports\OrderImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\URL;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReportController extends AdminController
{
    function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    /**
     * Apply common filters to order query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    private function applyOrderFilters($query)
    {
        if (request()->has('date_range') && !empty(request()->get('date_range'))) {
            switch (request()->get('date_range')) {
                case 'daily':
                    $query->where('delivery_date', '=', request()->get('daily_date'));
                    break;
                case 'this_week':
                    $query->where('delivery_date', '>=', date('Y-m-d', strtotime('last sunday')));
                    $query->where('delivery_date', '<=', date('Y-m-d', strtotime('next sunday')));
                    break;
                case 'this_month':
                    $query->where('delivery_date', '>=', date('Y-m-01'));
                    $query->where('delivery_date', '<=', date('Y-m-d'));
                    break;
                case 'custom':
                    $query->where('delivery_date', '>=', request()->get('start_date'));
                    $query->where('delivery_date', '<=', request()->get('end_date'));
                    break;
            }
        }

        // Driver ID filter
        if (request()->has('driver_id') && !empty(request()->get('driver_id'))) {
            if (is_array(request()->get('driver_id'))) {
                $driver_ids = array_filter(request()->get('driver_id'));
                if (!empty($driver_ids)) {
                    $query->whereIn('driver_id', $driver_ids);
                }
            } else {
                $query->where('driver_id', request()->get('driver_id'));
            }
        }

        // Order number range filter
        if (
            request()->has('sorder_no') && request()->get('sorder_no') != '' &&
            request()->has('eorder_no') && request()->get('eorder_no') != ''
        ) {

            $start_order = request()->get('sorder_no');
            $end_order = request()->get('eorder_no');

            // Add suffix if no dash present
            if (strpos($end_order, '-') === false) {
                $end_order .= '-999';
            }

            $query->whereBetween('id', [$start_order, $end_order]);
        }

        if (request()->has('delivery_date') && !empty(request()->get('delivery_date'))) {
            $query->where('delivery_date', request()->get('delivery_date'));
        }
    }

    /**
     * Print Daily Bank Sales Report
     *
     * @return \Illuminate\Http\Response
     */
    function printDailyBankSalesReport()
    {
        $query = Order::query();

        $query
            ->with([
                'driver',
                'customer',
                'address.mall',
                'address.area'
            ])
            ->orderBy('arrival_time')
            ->orderBy('id')
            ->orderBy('address_id');

        $this->applyOrderFilters($query);

        $orders_list = $query->get();

        // SPLIT list by payment method
        $this->v_data['daily_sales_list'] = array();
        foreach ($orders_list as $order) {
            $payment_method = $order->payment_method->key;
            if ($payment_method == '') {
                $payment_method = 'NULL_METHOD';
            }
            $payments_method = str_replace(array(' ', '-'), '_', $payment_method);
            $this->v_data['daily_sales_list']['date_'. date('Ymd', strtotime($order->delivery_date))]['payment_' . $payment_method][]  = $order;
            //$this->v_data['daily_sales_list']['payment_' . $payment_method][]  = $order;
        }
            //dd($this->v_data['daily_sales_list']);

        return view('admin.report.print_daily_bank_sales_report', $this->v_data);
    }

    /**
     * Print Quantity Order Daily Report
     *
     * @return \Illuminate\Http\Response
     */
    function printDailyOrderQuantityReport()
    {
        $query = Order::query();

        $query
            ->with([
                'driver',
                'customer',
                'address.mall',
                'address.area'
            ])
            ->orderBy('arrival_time')
            ->orderBy('id')
            ->orderBy('address_id');

        $this->applyOrderFilters($query);
    }

    /**
     * Print Monthly Sales Report
     *
     * @return \Illuminate\Http\Response
     */
    function printMonthlySalesReport()
    {
        $query = Order::query();

        $query
            ->with([
                'driver',
                'customer',
                'address.mall',
                'address.area'
            ])
            ->orderBy('arrival_time')
            ->orderBy('id')
            ->orderBy('address_id');

        $this->applyOrderFilters($query);
    }
}
