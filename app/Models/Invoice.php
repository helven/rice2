<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'status_id',
        'invoice_no',
        'ref_no',
        'billing_name',
        'billing_address',
        'tax_no',
        'subtotal',
        'delivery_fee',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'issue_date',
        'due_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the order that owns the invoice.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the status of the invoice.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(InvoiceStatus::class, 'status_id');
    }

    /**
     * Scope to get only active invoices.
     */
    public function scopeActive($query)
    {
        return $query->where('status_id', 1);
    }

    /**
     * Generate invoice number with padding
     *
     * @param int $invoiceId
     * @return string
     */
    public static function generateInvoiceNumber($invoiceId): string
    {
        $padding = config('app.order_id_padding', 5);
        return str_pad($invoiceId, $padding, '0', STR_PAD_LEFT);
    }
}