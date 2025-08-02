<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'customer_id',
        'address_id',
        'status_id',
        'payment_status_id',
        'payment_method_id',
        'delivery_date',
        'total_amount',
        'notes',
        'arrival_time',
        'dropoff_time',
        'driver_id',
        'driver_route',
        'backup_driver_id',
        'backup_driver_route',
        'driver_notes'
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id', 'id');
    }

    public function payment_status()
    {
        return $this->belongsTo(OrderStatus::class, 'payment_status_id', 'id');
    }

    public function payment_method()
    {
        return $this->belongsTo(AttrPaymentMethod::class, 'payment_method_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddressBook::class, 'address_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function backup_driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'backup_driver_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get the formatted order ID with zero padding and location suffix
     *
     * @return string
     */
    public function getFormattedIdAttribute(): string
    {
        $padding = config('app.order_id_padding', 5);
        $formattedId = str_pad($this->id, $padding, '0', STR_PAD_LEFT);

        // Append mall_id or area_id from address if available
        if ($this->address) {
            if ($this->address->mall_id) {
                $formattedId .= '-' . str_pad($this->address->mall_id, 3, '0', STR_PAD_LEFT);
            }
        }

        return $formattedId;
    }

    /**
     * Get the total quantity of all meals in this order
     *
     * @return int
     */
    public function getTotalQtyAttribute(): int
    {
        return $this->meals->sum(function ($meal) {
            return $meal->normal + $meal->big + $meal->small + $meal->s_small + $meal->no_rice;
        });
    }

    /**
     * Get the total quantity of all meals in this order
     *
     * @return int
     */
    public function getDeliveryFeeAttribute(): int
    {
        return $this->meals->sum(function ($meal) {
            return $meal->normal + $meal->big + $meal->small + $meal->s_small + $meal->no_rice;
        });
    }
}
