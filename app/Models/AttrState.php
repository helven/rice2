<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrState extends Model
{
    protected $table = 'attr_state';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}