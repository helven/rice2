<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryStatus extends Model
{
    protected $fillable = ['label', 'value'];
    
    const SCHEDULED = 1;
    const DELIVERED = 2;
    const CANCELLED = 3;
}
