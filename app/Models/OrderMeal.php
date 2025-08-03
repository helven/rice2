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
        'normal',
        'big',
        'small',
        's_small',
        'no_rice',
    ];

    protected $casts = [
        'normal' => 'integer',
        'big' => 'integer',
        'small' => 'integer',
        's_small' => 'integer',
        'no_rice' => 'integer',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(MealStatus::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Get the total quantity of a meal in this order
     *
     * @return int
     */
    public function getTotalQtyAttribute(): int
    {
        return $this->normal + $this->big + $this->small + $this->s_small + $this->no_rice;
    }
}