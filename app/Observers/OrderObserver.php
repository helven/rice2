<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InvoiceService;

class OrderObserver
{
    public function __construct(private InvoiceService $invoiceService)
    {
    }

    public function created(Order $order): void
    {
        \DB::afterCommit(function () use ($order) {
            $order->refresh();
            $this->invoiceService->handleOrderSaved($order);
        });
    }
    
    public function updated(Order $order): void
    {
        \DB::afterCommit(function () use ($order) {
            $order->refresh();
            $this->invoiceService->handleOrderSaved($order);
        });
    }
}
