<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrStatus extends Model
{
    protected $table = 'attr_status';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}