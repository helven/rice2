<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;

$dir = 'backend/';

Route::middleware(['web'])->prefix($base.$dir)->name('admin.')->group(function () {
    Route::get('order/print-dropoff', [OrderController::class, 'printDropOff'])->name('order.print_dropoff');
    Route::get('order/print-data', [OrderController::class, 'printData'])->name('order.print_data');
    Route::get('order/print-driver-sheet-1', [OrderController::class, 'printDriverSheet1'])->name('order.print_driver_sheet_1');
    Route::get('order/print-driver-sheet-2', [OrderController::class, 'printDriverSheet2'])->name('order.print_driver_sheet_2');
    Route::get('order/print-invoice/{invoice}', [OrderController::class, 'printInvoice'])->name('order.print_invoice');
    Route::get('report/print-daily-bank-sales-report', [ReportController::class, 'printDailyBankSalesReport'])->name('report.print_daily_bank_sales_report');
    Route::get('report/print-daily-order-quantity-report', [ReportController::class, 'printDailyOrderQuantityReport'])->name('report.print_daily_order_quantity_report');
    Route::get('report/print-monthly-sales-report', [ReportController::class, 'printMonthlySalesReport'])->name('report.print_monthly_sales_report');
});