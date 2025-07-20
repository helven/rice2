<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = 'order_statuses';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}