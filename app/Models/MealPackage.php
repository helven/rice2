<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'name',
        'main_image',
        'dish_images',
    ];

    protected $casts = [
        'dish_images' => 'array',
    ];

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}