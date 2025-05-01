<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'address_id',
        'status_id',
        'delivery_date',
        'total_amount',
        'notes',
        'arrival_time',
        'driver_id',
        'driver_route',
        'backup_driver_id',
        'backup_driver_route',
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function status()
    {
        return $this->belongsTo(AttrStatus::class, 'status_id', 'id');
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

    public function backupDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'backup_driver_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }
}