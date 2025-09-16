<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status_id',
        'category_id',
    ];

    protected $casts = [
        'route' => 'array',
    ];

    public function status()
    {
        return $this->belongsTo(MealStatus::class, 'status_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(MealCategory::class, 'category_id', 'id');
    }
}