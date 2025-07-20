<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerStatus extends Model
{
    protected $table = 'customer_statuses';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}