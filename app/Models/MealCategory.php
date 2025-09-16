<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealCategory extends Model
{
    protected $table = 'meal_categories';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'label'
    ];
}