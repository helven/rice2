<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MallStatus extends Model
{
    protected $table = 'mall_statuses';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}