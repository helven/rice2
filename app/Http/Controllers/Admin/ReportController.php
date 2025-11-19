<?php

namespace App\Http\Controllers\Admin;

use DB;
use File;
use App\Http\Controllers\AdminController;
use App\Models\AttrPaymentMethod;
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
                    $query->where('orders.order_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->get('daily_date'))));
                    $query->where('orders.order_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->get('daily_date'))));
                    break;
                case 'monthly':
                    $month = request()->get('month');
                    $query->where('orders.order_date', '>=', date('Y-m-01', strtotime($month)));
                    $query->where('orders.order_date', '<=', date('Y-m-t', strtotime($month)));
                    break;
                case 'this_week':
                    $query->where('orders.order_date', '>=', date('Y-m-d', strtotime('last sunday')));
                    $query->where('orders.order_date', '<=', date('Y-m-d', strtotime('next sunday')));
                    break;
                case 'this_month':
                    $query->where('orders.order_date', '>=', date('Y-m-01'));
                    $query->where('orders.order_date', '<=', date('Y-m-d'));
                    break;
                case 'custom':
                    $query->where('orders.order_date', '>=', request()->get('start_date'));
                    $query->where('orders.order_date', '<=', request()->get('end_date'));
                    break;
            }
        }

        if (request()->has('delivery_date') && !empty(request()->get('delivery_date'))) {
            $query->where('orders.order_date', request()->get('delivery_date'));
        }
    }

    /**
     * Apply common filters to delivery query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    private function applyDeliveryFilters($query)
    {
        if (request()->has('date_range') && !empty(request()->get('date_range'))) {
            switch (request()->get('date_range')) {
                case 'daily':
                    $query->where('deliveries.delivery_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->get('daily_date'))));
                    $query->where('deliveries.delivery_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->get('daily_date'))));
                    break;
                case 'monthly':
                    $month = request()->get('month');
                    $query->where('deliveries.delivery_date', '>=', date('Y-m-01', strtotime($month)));
                    $query->where('deliveries.delivery_date', '<=', date('Y-m-t', strtotime($month)));
                    break;
                case 'this_week':
                    $query->where('deliveries.delivery_date', '>=', date('Y-m-d', strtotime('last sunday')));
                    $query->where('deliveries.delivery_date', '<=', date('Y-m-d', strtotime('next sunday')));
                    break;
                case 'this_month':
                    $query->where('deliveries.delivery_date', '>=', date('Y-m-01'));
                    $query->where('deliveries.delivery_date', '<=', date('Y-m-d'));
                    break;
                case 'custom':
                    $query->where('deliveries.delivery_date', '>=', request()->get('start_date'));
                    $query->where('deliveries.delivery_date', '<=', request()->get('end_date'));
                    break;
            }
        }

        if (request()->has('delivery_date') && !empty(request()->get('delivery_date'))) {
            $query->where('deliveries.delivery_date', request()->get('delivery_date'));
        }
    }

    /**
     * Apply common filters to delivery query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    private function applyGeneralFilters($query)
    {
        // Driver ID filter
        if (request()->has('driver_id') && !empty(request()->get('driver_id'))) {
            if (is_array(request()->get('driver_id'))) {
                $driverIds = array_filter(request()->get('driver_id'));
                if (!empty($driverIds)) {
                    $query->whereIn('deliveries.driver_id', $driverIds);
                }
            } else {
                $query->where('deliveries.driver_id', request()->get('driver_id'));
            }
        }

        // Order number range filter
        if (
            request()->has('sorder_no') && request()->get('sorder_no') != '' &&
            request()->has('eorder_no') && request()->get('eorder_no') != ''
        ) {

            $startOrder = request()->get('sorder_no');
            $endOrder = request()->get('eorder_no');

            // Add suffix if no dash present
            if (strpos($endOrder, '-') === false) {
                $endOrder .= '-999';
            }

            $query->whereBetween('id', [$startOrder, $endOrder]);
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
            ->whereHas('deliveries', function($q) {
                $this->applyOrderFilters($q);
                $this->applyGeneralFilters($q);
            })
            ->with([
                'customer',
                'deliveries' => function($q) {
                    $q->orderBy('id')->limit(1);
                },
                'deliveries.driver',
                'deliveries.address.mall',
                'deliveries.address.area',
                'payment_method'
            ]);

        $ordersList = $query->get();
        //dd($ordersList->toArray());

        // SPLIT list by payment method
        $this->vData['daily_sales_list'] = array();
        foreach ($ordersList as $order) {
            $paymentMethod = $order->payment_method->key;
            if ($paymentMethod == '') {
                $paymentMethod = 'NULL_METHOD';
            }
            $this->vData['daily_sales_list']['date_'.date('Ymd', strtotime($order->created_at))]['payment_' . $paymentMethod][]  = $order;
            //$this->vData['daily_sales_list']['payment_' . $paymentMethod][]  = $order;
        }
            //dd($this->vData['daily_sales_list']);

        return view('admin.report.print_daily_bank_sales_report', $this->vData);
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
            ->whereHas('deliveries', function($q) {
                $this->applyOrderFilters($q);
            })
            ->with([
                'customer',
                'deliveries' => function($q) {
                    $q->orderBy('id')->limit(1);
                },
                'deliveries.driver',
                'deliveries.address.mall',
                'deliveries.address.area',
                'meals.meal'
            ]);

        $ordersList = $query->get();

        // SPLIT list by meal
        $this->vData['daily_qty_list'] = array();
        foreach ($ordersList as $order) {
            foreach ($order->meals as $orderMeal) {
                $this->vData['daily_qty_list']['meal_'.$orderMeal->meal->code]  = array(
                    'code' => $orderMeal->meal->code,
                    'date' => $order->delivery_date,
                    'meal' => $orderMeal->meal->name,
                    'normal' => $orderMeal->normal,
                    'big' => $orderMeal->big,
                    'small' => $orderMeal->small,

                    'no_rice' => $orderMeal->no_rice,
                );
            }
        }

        return view('admin.report.print_daily_qty_report', $this->vData);
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
            ->whereHas('deliveries', function($q) {
                $this->applyOrderFilters($q);
            })
            ->with([
                'customer',
                'deliveries' => function($q) {
                    $q->orderBy('id')->limit(1);
                },
                'deliveries.driver',
                'deliveries.address.mall',
                'deliveries.address.area',
                'payment_method'
            ]);

        $ordersList = $query->get();

        // SPLIT list by day method
        $this->vData['monthly_sales_list'] = array();
        $month = request()->get('month');
        $lastDay = date('t', strtotime($month));
        $this->vData['payment_methods'] = AttrPaymentMethod::pluck('key');

        for ($i = 1; $i < $lastDay; $i++) {
            $this->vData['monthly_sales_list']['date_'. date('Ymd', strtotime($month.'-'.$i))] = array();
            $this->vData['monthly_sales_list']['date_'. date('Ymd', strtotime($month.'-'.$i))]['date'] = date('Y-m-d', strtotime($month.'-'.$i));
            foreach($this->vData['payment_methods'] as $paymentMethod) {
                $this->vData['monthly_sales_list']['date_'.date('Ymd', strtotime($month.'-'.$i))]['payment_'.$paymentMethod] = 0;
            }
        }

        foreach ($ordersList as $order) {
            if($order->payment_status_id == 4){
                $this->vData['monthly_sales_list']['date_'.date('Ymd', strtotime($order->delivery_date))]['payment_'.$order->payment_method->key] += $order->total_amount;
            }
            //$this->vData['monthly_sales_list']['date_'. date('Ymd', strtotime($order->delivery_date))][]  = $order;
        }

        return view('admin.report.print_monthly_sales_report', $this->vData);
    }
}
