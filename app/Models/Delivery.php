<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_no',
        'deliverable_id',
        'customer_id',
        'address_id',
        'delivery_date',
        'arrival_time',
        'dropoff_time',
        'driver_id',
        'driver_route',
        'backup_driver_id',
        'backup_driver_route',
        'driver_notes',
        'delivery_proof',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($delivery) {
            if (empty($delivery->delivery_no)) {
                $delivery->delivery_no = static::generateDeliveryNo();
            }
        });
    }

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_proof' => 'array',
    ];

    // Simple relationship to Order (regardless of type)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'deliverable_id');
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

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('delivery_date', $date);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    // Generate delivery number
    public static function generateDeliveryNo(): string
    {
        $year = date('Y');
        $lastDelivery = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDelivery) {
            $lastNumber = (int) substr($lastDelivery->delivery_no, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'D' . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getTotalQtyAttribute()
    {
        return $this->order?->total_qty ?? 0;
    }
}