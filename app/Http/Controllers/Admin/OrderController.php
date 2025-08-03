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

class OrderController extends AdminController
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
                    $query->where('delivery_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->get('daily_date'))));
                    $query->where('delivery_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->get('daily_date'))));
                    break;
                case 'this_week':
                    $query->where('delivery_date', '>=', date('Y-m-d', strtotime('last sunday')));
                    $query->where('delivery_date', '<=', date('Y-m-d', strtotime('next sunday')));
                    break;
                case 'this_month':
                    $query->where('delivery_date', '>=', date('Y-m-01 00:00:00'));
                    $query->where('delivery_date', '<=', date('Y-m-d 23:59:59'));
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
                $driverIds = array_filter(request()->get('driver_id'));
                if (!empty($driverIds)) {
                    $query->whereIn('driver_id', $driverIds);
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

            $startOrder = request()->get('sorder_no');
            $endOrder = request()->get('eorder_no');

            // Add suffix if no dash present
            if (strpos($endOrder, '-') === false) {
                $endOrder .= '-999';
            }

            $query->whereBetween('id', [$startOrder, $endOrder]);
        }

        if (request()->has('delivery_date') && !empty(request()->get('delivery_date'))) {
            $query->where('delivery_date', request()->get('delivery_date'));
        }
    }

    /**
     * Print Order Data
     *
     * @return \Illuminate\Http\Response
     */
    function printData()
    {
        $query = Order::query();

        $query
            ->with([
                'meals.meal',
                'driver',
                'customer',
                'address.mall',
                'address.area'
            ])
            ->orderBy('arrival_time')
            ->orderBy('id')
            ->orderBy('address_id');

        $this->applyOrderFilters($query);

        $ordersList = $query->get();

        $this->vData['orders_list'] = $ordersList;

        return view('admin.order.print_data', $this->vData);
    }

    /**
     * Print Drop Off
     *
     * @return \Illuminate\Http\Response
     */
    function printDropOff()
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

        $ordersList = $query->get();
        // SPLIT list by driver
        $this->vData['orders_list'] = array();
        foreach ($ordersList as $order) {
            $this->vData['orders_list']['driver_' . $order->driver->id][]  = $order;
        }

        $ordersPerDriver  = 20;
        foreach ($this->vData['orders_list']  as $driverId => &$driver) {
            if (count($driver) > $ordersPerDriver ) {
                $aTemp         = array();
                $orderCtr      = 0;
                $separateCtr   = 0;

                foreach ($driver as $order) {
                    if ($orderCtr < $ordersPerDriver ) {
                        array_push($aTemp, $order);
                    } else {
                        $this->vData['orders_list'][$driverId . '-' . $separateCtr] = $aTemp;
                        $separateCtr++;

                        // RESET
                        $aTemp = array();
                        $orderCtr    = 0;
                        array_push($aTemp, $order);
                    }
                    $orderCtr++;
                }
                $this->vData['orders_list'][$driverId . '-' . $separateCtr] = $aTemp;
                unset($this->vData['orders_list'][$driverId]);
            }
        }
        ksort($this->vData['orders_list']); // sort by [driver_name]_[driver_id]-ctr

        return view('admin.order.print_dropoff', $this->vData);
    }

    /**
     * Print Driver Sheet 1
     *
     * @return \Illuminate\Http\Response
     */
    function printDriverSheet1()
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

        $ordersList = $query->get();

        // SPLIT list by driver
        $this->vData['orders_list'] = array();
        foreach ($ordersList as $order) {
            $this->vData['orders_list']['driver_' . $order->driver->id][]  = $order;
        }

        $ordersPerDriver  = 3;
        foreach ($this->vData['orders_list']  as $driverId => &$driver) {
            if (count($driver) > $ordersPerDriver ) {
                $aTemp = array();
                $orderCtr = 0;
                $separateCtr = 0;

                foreach ($driver as $order) {
                    if ($orderCtr < $ordersPerDriver ) {
                        array_push($aTemp, $order);
                    } else {
                        $this->vData['orders_list'][$driverId . '-' . $separateCtr] = $aTemp;
                        $separateCtr++;

                        // RESET
                        $aTemp = array();
                        $orderCtr = 0;
                        array_push($aTemp, $order);
                    }
                    $orderCtr++;
                }
                $this->vData['orders_list'][$driverId . '-' . $separateCtr] = $aTemp;
                unset($this->vData['orders_list'][$driverId]);
            }
        }
        ksort($this->vData['orders_list']); // sort by [driver_name]_[driver_id]-ctr

        return view('admin.order.print_driver_sheet_1', $this->vData);
    }

    /**
     * Print Driver Sheet 2
     *
     * @return \Illuminate\Http\Response
     */
    function printDriverSheet2()
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

        $ordersList = $query->get();

        $this->vData['orders_list'] = $ordersList;

        return view('admin.order.print_driver_sheet_2', $this->vData);
    }

    /**
     * Print Invoice
     *
     * @return \Illuminate\Http\Response
     */
    function printInvoice($order_id) {
        $query = Order::query();

        $query
            ->with([
                'meals.meal',
                'customer',
            ])
            ->leftjoin('invoices', 'invoices.order_id', '=', 'orders.id')
            ->where('orders.id', $order_id);

        $order = $query->get();

        $this->vData['order'] = $order;

        return view('admin.order.print_invoice', $this->vData);
    }
}
