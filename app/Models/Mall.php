<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mall extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'name',
        'payment_medthod_id'
    ];

    public function status()
    {
        return $this->belongsTo(AttrStatus::class, 'status_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(AttrPaymentMethod::class, 'payment_medthod_id', 'id');
    }
}