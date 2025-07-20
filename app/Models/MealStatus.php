<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealStatus extends Model
{
    protected $table = 'meal_statuses';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}