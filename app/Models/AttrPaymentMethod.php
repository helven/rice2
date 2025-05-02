<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrPaymentMethod extends Model
{
    protected $table = 'attr_payment_method';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}