<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeal extends Model
{
    protected $fillable = [
        'status_id',
        'order_id',
        'meal_id',
        'normal_rice',
        'small_rice',
        'no_rice',
        'vegi',
    ];

    protected $casts = [
        'normal_rice' => 'integer',
        'small_rice' => 'integer',
        'no_rice' => 'integer',
        'vegi' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}