<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Order;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/orders/existing-delivery-dates', function (Request $request) {
    $customerId = $request->query('customer_id');
    $addressId = $request->query('address_id');
    $excludeOrderId = $request->query('exclude_order_id'); // Optional parameter for Edit Order
    
    if (!$customerId || !$addressId) {
        return response()->json(['dates' => []]);
    }
    
    $query = Order::where('customer_id', $customerId)
        ->where('address_id', $addressId)
        ->whereNotNull('delivery_date');
    
    // Exclude the current order if editing
    if ($excludeOrderId) {
        $query->where('id', '!=', $excludeOrderId);
    }
    
    $existingDates = $query->pluck('delivery_date')
        ->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        })
        ->unique()
        ->values()
        ->toArray();
    
    return response()->json(['dates' => $existingDates]);
});
