<?php

namespace App\Http\Controllers\Admin;

use DB;
use File;
use App\Http\Controllers\AdminController;
//use App\Models\OrderBatch;
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
                case 'today':
                    $query->where('delivery_date', '=', date('Y-m-d'));
                    break;
                case 'week':
                    $query->where('delivery_date', '>=', date('Y-m-d', strtotime('last sunday')));
                    $query->where('delivery_date', '<=', date('Y-m-d', strtotime('next sunday')));
                    break;
                case 'month':
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

        $orders_list = $query->get();

        $this->v_data['orders_list'] = $orders_list;

        return view('admin.order.print_data', $this->v_data);
    }

    /**
     * Print Driver Sheet
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

        $orders_list = $query->get();
        // SPLIT list by driver
        $this->v_data['orders_list'] = array();
        foreach ($orders_list as $order) {
            $this->v_data['orders_list']['driver_' . $order->driver->id][]  = $order;
        }

        $orders_per_driver = 20;
        foreach ($this->v_data['orders_list']  as $driver_id => &$driver) {
            if (count($driver) > $orders_per_driver) {
                $a_temp         = array();
                $order_ctr      = 0;
                $separate_ctr   = 0;

                foreach ($driver as $order) {
                    if ($order_ctr < $orders_per_driver) {
                        array_push($a_temp, $order);
                    } else {
                        $this->v_data['orders_list'][$driver_id . '-' . $separate_ctr] = $a_temp;
                        $separate_ctr++;

                        // RESET
                        $a_temp = array();
                        $order_ctr    = 0;
                        array_push($a_temp, $order);
                    }
                    $order_ctr++;
                }
                $this->v_data['orders_list'][$driver_id . '-' . $separate_ctr] = $a_temp;
                unset($this->v_data['orders_list'][$driver_id]);
            }
        }
        ksort($this->v_data['orders_list']); // sort by [driver_name]_[driver_id]-ctr

        return view('admin.order.print_dropoff', $this->v_data);
    }

    /**
     * Print Driver Sheet
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

        $orders_list = $query->get();

        // SPLIT list by driver
        $this->v_data['orders_list'] = array();
        foreach ($orders_list as $order) {
            $this->v_data['orders_list']['driver_' . $order->driver->id][]  = $order;
        }

        $orders_per_driver = 3;
        foreach ($this->v_data['orders_list']  as $driver_id => &$driver) {
            if (count($driver) > $orders_per_driver) {
                $a_temp         = array();
                $order_ctr      = 0;
                $separate_ctr   = 0;

                foreach ($driver as $order) {
                    if ($order_ctr < $orders_per_driver) {
                        array_push($a_temp, $order);
                    } else {
                        $this->v_data['orders_list'][$driver_id . '-' . $separate_ctr] = $a_temp;
                        $separate_ctr++;

                        // RESET
                        $a_temp = array();
                        $order_ctr    = 0;
                        array_push($a_temp, $order);
                    }
                    $order_ctr++;
                }
                $this->v_data['orders_list'][$driver_id . '-' . $separate_ctr] = $a_temp;
                unset($this->v_data['orders_list'][$driver_id]);
            }
        }
        ksort($this->v_data['orders_list']); // sort by [driver_name]_[driver_id]-ctr

        return view('admin.order.print_driver_sheet_1', $this->v_data);
    }

    /**
     * Print Driver Sheet
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

        $orders_list = $query->get();

        $this->v_data['orders_list'] = $orders_list;

        return view('admin.order.print_driver_sheet_2', $this->v_data);
    }
    /**
     * Print Order Data
     *
     * @return \Illuminate\Http\Response
     */
    function printPayment()
    {
        //\Debugbar::disable();

        $m_import_order = new Order();

        $where  = '';
        $a_qvar = array();

        if (request()->has('batch_id') && !empty(request()->get('batch_id'))) {
            $where  .= "    AND import_orders_batch.id = :id";
            $a_qvar['id']   = request()->get('batch_id');
        }

        $this->v_data['import_orders_batch']   = $m_import_order->getOrderBatch(array(
            'where' => $where,
            'qvar'  => $a_qvar
        ));

        $where  = '';
        $a_qvar = array();

        if (request()->has('batch_id') && !empty(request()->get('batch_id'))) {
            $where  .= "    AND import_orders.batch_id = :batch_id";
            $a_qvar['batch_id']   = request()->get('batch_id');
        }
        if (request()->has('driver_id') && !empty(request()->get('driver_id'))) {
            if (is_array(request()->get('driver_id'))) {
                $driver_id = implode(',', request()->get('driver_id'));
                if ($driver_id != '') {
                    $where  .= "    AND import_orders.driver_id IN({$driver_id})";
                }
            } else {
                $where  .= "    AND import_orders.driver_id = :driver_id";
                $a_qvar['driver_id']    = request()->get('driver_id');
            }
        }
        if (request()->has('sorder_no') && request()->get('sorder_no') != '' && request()->has('eorder_no') && request()->get('eorder_no') != '') {
            $where  .= "    AND import_orders.order_no BETWEEN :sorder_no AND :eorder_no";
            $a_qvar['sorder_no']     = request()->get('sorder_no');
            // if(strpos($a_qvar['sorder_no'], '-') === FALSE){
            //     $a_qvar['sorder_no']    .= '-000';
            // }

            $a_qvar['eorder_no']    = request()->get('eorder_no');
            if (strpos($a_qvar['eorder_no'], '-') === FALSE) {
                $a_qvar['eorder_no']    .= '-999';
            }
        }

        $import_orders_list = $m_import_order->getOrderList(array(
            'where' => $where,
            'qvar'  => $a_qvar,
            'order' => "    ORDER BY payment_method ASC, id ASC"
        ));

        // SPLIT list by batch id method
        $a_temp = array();
        foreach ($import_orders_list as $import_orders) {
            $a_temp['batch_' . $import_orders->batch_id][]  = $import_orders;
        }

        // SPLIT list by payment method
        $this->v_data['import_orders_list'] = array();
        foreach ($a_temp as $key => $a_batch) {
            foreach ($a_batch as $import_orders) {
                $payment_method = $import_orders->payment_method;
                if ($payment_method == '') {
                    $payment_method = 'NULL_METHOD';
                }
                $payment_method = str_replace(array(' ', '-'), '_', $payment_method);
                $this->v_data['import_orders_list'][$key]['payment_' . $payment_method][]  = $import_orders;
            }
        }

        return view('admin.order.print_payment', $this->v_data);
    }
}
