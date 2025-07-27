<?php
$dir = 'backend/';

Route::get($base.$dir.'orders/print-dropoff', [App\Http\Controllers\Admin\OrderController::class, 'printDropOff'])->name('admin.order.print_dropoff');
Route::get($base.$dir.'orders/print-data', [App\Http\Controllers\Admin\OrderController::class, 'printData'])->name('admin.order.print_data');
Route::get($base.$dir.'orders/print-driver-sheet-1', [App\Http\Controllers\Admin\OrderController::class, 'printDriverSheet1'])->name('admin.order.print_driver_sheet_1');
Route::get($base.$dir.'orders/print-driver-sheet-2', [App\Http\Controllers\Admin\OrderController::class, 'printDriverSheet2'])->name('admin.order.print_driver_sheet_2');
Route::get($base.$dir.'orders/print-payment', [App\Http\Controllers\Admin\OrderController::class, 'printPayment'])->name('admin.order.print_payment');