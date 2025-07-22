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
     * Print Order Data
     *
     * @return \Illuminate\Http\Response
     */
    function printData()
    {
        \Debugbar::disable();

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

        $this->v_data['import_orders_list']  = $m_import_order->getOrderList(array(
            'where' => $where,
            'qvar'  => $a_qvar,
            'order' => "    ORDER BY driver_name ASC, import_orders_batch.order_date ASC, import_orders.order_no ASC",
        ));

        return view('admin.import_order.print_data', $this->v_data);
    }

    /**
     * Print Driver Sheet
     *
     * @return \Illuminate\Http\Response
     */
    function printDropOff()
    {
        \Debugbar::disable();

        $query = Order::query();

        $query
            ->select(
                'orders.*',
                'drivers.name as driver_name',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customer_address_books.name as address_name',
                'customer_address_books.mall_id as address_mall_id',
                'malls.name as mall_name',
                'customer_address_books.area_id as address_area_id',
                'areas.name as area_name',
                'customer_address_books.address_1 as address_1',
                'customer_address_books.address_2 as address_2',
                'customer_address_books.postal_code as address_postal_code',
                'customer_address_books.city as address_city',
                'customer_address_books.state_id as address_state_id',
                'customer_address_books.country_id as address_country_id',
            )
            ->leftJoin('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('customer_address_books', 'orders.address_id', '=', 'customer_address_books.id')
            ->leftJoin('malls', 'customer_address_books.mall_id', '=', 'malls.id')
            ->leftJoin('areas', 'customer_address_books.area_id', '=', 'areas.id')
            ->orderBy('arrival_time')
            ->orderBy('order_no')
            ->orderBy('address_id');

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

        return view('admin.order.print_dropoff', $this->v_data);
    }

    /**
     * Print Driver Sheet
     *
     * @return \Illuminate\Http\Response
     */
    function printDriverSheet1()
    {
        \Debugbar::disable();

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
            'order' => "    ORDER BY driver_name ASC, arrival_time ASC, order_no ASC, destination ASC"
        ));

        // SPLIT list by batch id method
        $a_temp = array();
        foreach ($import_orders_list as $import_orders) {
            $a_temp['batch_' . $import_orders->batch_id][]  = $import_orders;
        }

        // SPLIT list by driver
        $this->v_data['import_orders_list'] = array();
        foreach ($a_temp as $key => $a_batch) {
            foreach ($a_batch as $import_orders) {
                $driver_name = $import_orders->driver_name;
                if (!is_numeric($driver_name[strlen($driver_name) - 1])) {
                    $driver_name = $driver_name . '0';
                }
                $this->v_data['import_orders_list'][$key]['driver_' . $driver_name . '_' . $import_orders->driver_id][]  = $import_orders;
            }
        }

        // SEPARATE order if more than 20 per driver
        $orders_per_driver = 20;
        foreach ($this->v_data['import_orders_list'] as $batch_key => &$batch) {
            foreach ($batch as $driver_key => &$driver) {
                if (count($driver) > $orders_per_driver) {
                    $a_temp         = array();
                    $order_ctr      = 0;
                    $separate_ctr   = 0;

                    foreach ($driver as $order) {
                        if ($order_ctr < $orders_per_driver) {
                            array_push($a_temp, $order);
                        } else {
                            $this->v_data['import_orders_list'][$batch_key][$driver_key . '-' . $separate_ctr] = $a_temp;
                            $separate_ctr++;

                            // RESET
                            $a_temp = array();
                            $order_ctr    = 0;
                            array_push($a_temp, $order);
                        }
                        $order_ctr++;
                    }
                    $this->v_data['import_orders_list'][$batch_key][$driver_key . '-' . $separate_ctr] = $a_temp;
                    unset($this->v_data['import_orders_list'][$batch_key][$driver_key]);
                }
            }
            ksort($batch); // sort by [driver_name]_[driver_id]-ctr
        }

        return view('admin.import_order.print_driver_sheet_1', $this->v_data);
    }

    /**
     * Print Driver Sheet
     *
     * @return \Illuminate\Http\Response
     */
    function printDriverSheet2()
    {
        \Debugbar::disable();

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

        $this->v_data['import_orders_list']  = $m_import_order->getOrderList(array(
            'where' => $where,
            'qvar'  => $a_qvar,
            'order' => "    ORDER BY drivers.name ASC, import_orders.order_no ASC"
        ));

        return view('admin.import_order.print_driver_sheet_2', $this->v_data);
    }
    /**
     * Print Order Data
     *
     * @return \Illuminate\Http\Response
     */
    function printPayment()
    {
        \Debugbar::disable();

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

        return view('admin.import_order.print_payment', $this->v_data);
    }
}
