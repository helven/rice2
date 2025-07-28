<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;

$dir = 'backend/';

Route::middleware(['web'])->prefix($base.$dir)->name('admin.')->group(function () {
    Route::get('orders/print-dropoff', [OrderController::class, 'printDropOff'])->name('order.print_dropoff');
    Route::get('orders/print-data', [OrderController::class, 'printData'])->name('order.print_data');
    Route::get('orders/print-driver-sheet-1', [OrderController::class, 'printDriverSheet1'])->name('order.print_driver_sheet_1');
    Route::get('orders/print-driver-sheet-2', [OrderController::class, 'printDriverSheet2'])->name('order.print_driver_sheet_2');
    Route::get('orders/print-payment', [OrderController::class, 'printPayment'])->name('order.print_payment');
});