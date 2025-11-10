<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceStatus extends Model
{
    protected $fillable = ['label', 'description', 'is_system'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'status_id');
    }
}
