<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_no',
        'ref_no',
        'billing_name',
        'billing_address',
        'tax_no',
        'tax_amount',
        'issue_date',
        'due_date',
    ];

    protected $casts = [
        'tax_amount' => 'decimal:2',
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
}