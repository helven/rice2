<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;

class InvoiceService
{
    public function handleOrderSaved(Order $order): void
    {
        $activeInvoice = $order->invoices()->where('status_id', 1)->first();
        
        $currentData = $this->calculateFinancialData($order);

        // If order has no invoice create yet, create a new invoice
        if (!$activeInvoice) {
            $this->createInvoice($order, $currentData);
            return;
        }

        // Check if existing invoice subtotal, delivery_fee, tax_rate is different from current order value
        // Void existing and create new one if yes
        if ($this->hasFinancialChanges($activeInvoice, $currentData)) {
            $activeInvoice->update(['status_id' => 2]);
            $this->createInvoice($order, $currentData);
        }
    }

    
    private function calculateFinancialData(Order $order): array
    {
        $subtotal = $order->total_amount ?? 0;
        $deliveryFee = $order->delivery_fee ?? 0;
        $taxRate = config('app.tax_rate', 0);
        $taxAmount = ($subtotal + $deliveryFee) * ($taxRate / 100);
        $totalAmount = $subtotal + $deliveryFee + $taxAmount;
        
        return [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'tax_rate' => round($taxRate, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }
    
    private function hasFinancialChanges(Invoice $invoice, array $currentData): bool
    {
        return $invoice->subtotal != $currentData['subtotal']
            || $invoice->delivery_fee != $currentData['delivery_fee']
            || $invoice->tax_rate != $currentData['tax_rate'];
    }
    
    private function createInvoice(Order $order, array $data): Invoice
    {
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'status_id' => 1,
            'invoice_no' => 'TEMP',
            'subtotal' => $data['subtotal'],
            'delivery_fee' => $data['delivery_fee'],
            'tax_rate' => $data['tax_rate'],
            'tax_amount' => $data['tax_amount'],
            'total_amount' => $data['total_amount'],
            'issue_date' => now(),
        ]);
        
        $invoice->update(['invoice_no' => $this->generateInvoiceNo($invoice)]);
        
        return $invoice;
    }
    
    private function generateInvoiceNo(Invoice $invoice): string
    {
        $padding = config('app.order_id_padding', 5);
        return str_pad($invoice->id, $padding, '0', STR_PAD_LEFT);
    }
}
