<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'order_type',
        'customer_id',
        'status_id',
        'payment_status_id',
        'payment_method_id',
        'total_amount',
        'delivery_fee',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
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

    /**
     * Get address from delivery record
     */
    public function getAddressAttribute()
    {
        $delivery = $this->getDelivery();
        return $delivery ? CustomerAddressBook::find($delivery->address_id) : null;
    }



    public function meals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'deliverable_id')
            ->whereIn('deliverable_type', ['single', 'meal_plan']);
    }

    /**
     * Get delivery_date from the first delivery record
     */
    public function getDeliveryDateAttribute()
    {
        return $this->deliveries->first()?->delivery_date;
    }

    /**
     * Get delivery record for this order
     */
    public function getDelivery()
    {
        return \App\Models\Delivery::where('deliverable_id', $this->id)
            ->whereIn('deliverable_type', ['single', 'meal_plan'])
            ->first();
    }



    /**
     * Get formatted order ID with padding
     *
     * @param int $orderId
     * @return string
     */
    public static function getFormattedOrderId($orderId): string
    {
        $padding = config('app.order_id_padding', 5);
        return str_pad($orderId, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Get the formatted order ID with zero padding and location suffix
     *
     * @return string
     */
    public function getFormattedIdAttribute(): string
    {
        $formattedId = self::getFormattedOrderId($this->id);

        // Append mall_id or area_id from address if available
        if ($this->address) {
            if ($this->address->mall_id) {
                $formattedId .= '-' . str_pad($this->address->mall_id, 3, '0', STR_PAD_LEFT);
            }
        }

        return $formattedId;
    }

    /**
     * Get the invoice no from order ID with zero padding
     *
     * @return string
     */
    public function getInvoiceNoAttribute(): string
    {
        $padding = config('app.order_id_padding', 5);
        $formattedId = str_pad($this->id, $padding, '0', STR_PAD_LEFT);

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
     * Calculate delivery fee based on total quantity and area delivery fee rules
     *
     * @return float
     */
    public function getCalculatedDeliveryFeeAttribute(): float
    {
        if (!$this->address || !$this->address->area) {
            return 0;
        }

        $totalQuantity = $this->meals->sum(function ($meal) {
            return $meal->normal + $meal->big + $meal->small + $meal->s_small + $meal->no_rice;
        });

        $deliveryFeeRules = $this->address->area->delivery_fee;
        
        if (!is_array($deliveryFeeRules)) {
            return 0;
        }

        foreach ($deliveryFeeRules as $rule) {
            if (isset($rule['qty']) && $totalQuantity <= $rule['qty']) {
                return $rule['delivery_fee'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Generate order number in format: [orders.id]-[customers.mall_id]-[daily counter] or just [orders.id]
     * Example: 00000001-001-1 or 00000001
     *
     * @param int $orderId
     * @param int|null $mallId
     * @param string|null $deliveryDate
     * @return string
     */
    public static function generateOrderNumber($orderId, $mallId = null, $deliveryDate = null)
    {
        // Use the formatted order ID method
        $formattedOrderId = self::getFormattedOrderId($orderId);
        
        // If no mall ID, return just the formatted order ID
        if (!$mallId) {
            return $formattedOrderId;
        }
        
        // Format mall ID with 3 digits padding
        $formattedMallId = str_pad($mallId, 3, '0', STR_PAD_LEFT);
        
        // Get daily counter for this mall and delivery date, excluding current order
        $dailyCounter = self::getDailyCounter($mallId, $deliveryDate, $orderId);
        
        // Log the daily counter value for debugging
        \Log::info("Daily Counter Debug", [
            'order_id' => $orderId,
            'mall_id' => $mallId,
            'delivery_date' => $deliveryDate,
            'daily_counter' => $dailyCounter
        ]);
        
        return "{$formattedOrderId}-{$formattedMallId}-{$dailyCounter}";
    }

    /**
     * Get the daily counter for orders in a specific mall on a specific date
     *
     * @param int $mallId
     * @param string $deliveryDate
     * @param int|null $excludeOrderId Optional order ID to exclude from count
     * @return int
     */
    private static function getDailyCounter($mallId, $deliveryDate, $excludeOrderId = null)
    {
        // Use deliveries table for address filtering
        $query = self::select('orders.id')
            ->join('deliveries', function($join) {
                $join->on('orders.id', '=', 'deliveries.deliverable_id')
                     ->where('deliveries.deliverable_type', '=', 'order');
            })
            ->join('customer_address_books', 'deliveries.address_id', '=', 'customer_address_books.id')
            ->where('customer_address_books.mall_id', $mallId)
            ->whereDate('deliveries.delivery_date', $deliveryDate);
        
        // Exclude the current order if specified
        if ($excludeOrderId) {
            $query->where('orders.id', '!=', $excludeOrderId);
        }
        
        $existingCount = $query->count();
        
        // Return the next counter (existing count + 1)
        return $existingCount + 1;
    }
}
