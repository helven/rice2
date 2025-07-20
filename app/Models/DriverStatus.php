<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverStatus extends Model
{
    protected $table = 'driver_statuses';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}