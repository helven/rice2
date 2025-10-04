<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\CustomerAddressBook;

class DeliveryService
{
    /**
     * Create delivery record for an order
     */
    public function createOrderDelivery(Order $order): Delivery
    {
        return Delivery::create([
            'deliverable_type' => 'order',
            'deliverable_id' => $order->id,
            'customer_id' => $order->customer_id,
            'address_id' => $order->address_id,
            'delivery_date' => $order->delivery_date,
            'arrival_time' => $order->arrival_time,
            'driver_id' => $order->driver_id,
            'driver_route' => $order->driver_route,
            'backup_driver_id' => $order->backup_driver_id ?: null,
            'driver_notes' => $order->driver_notes,
        ]);
    }

    /**
     * Calculate delivery fee based on address and quantity
     */
    public function calculateDeliveryFee(CustomerAddressBook $address, int $totalQty): float
    {
        if ($address && $address->mall_id) {
            return 0;
        }

        if (!$address || !$address->area_id) {
            return 0;
        }

        $area = $address->area;
        if (!$area || !$area->delivery_fee) {
            return 0;
        }

        $deliveryFeeRules = $area->delivery_fee;

        // Sort by qty in descending order to find the highest applicable tier
        usort($deliveryFeeRules, function ($a, $b) {
            return $b['qty'] - $a['qty'];
        });

        // Find the appropriate delivery fee based on total quantity
        foreach ($deliveryFeeRules as $rule) {
            if ($totalQty >= $rule['qty']) {
                return $rule['delivery_fee'];
            }
        }

        // If no rule matches, return the fee for the lowest quantity tier
        return end($deliveryFeeRules)['delivery_fee'] ?? 0;
    }

    /**
     * Get deliveries for a specific date
     */
    public function getDeliveriesForDate($date, $driverId = null)
    {
        $query = Delivery::with(['deliverable', 'customer', 'address', 'driver', 'backupDriver'])
            ->forDate($date)
            ->orderBy('arrival_time');

        if ($driverId) {
            $query->forDriver($driverId);
        }

        return $query->get();
    }

    /**
     * Update delivery information
     */
    public function updateDelivery(Delivery $delivery, array $data): void
    {
        $delivery->update($data);
    }

    /**
     * Get delivery data for an order
     */
    public function getOrderDelivery(Order $order): ?Delivery
    {
        return $order->deliveries()->first();
    }

    /**
     * Get combined delivery data (orders + deliveries)
     */
    public function getCombinedDeliveryData(Order $order): array
    {
        $delivery = $this->getOrderDelivery($order);
        
        return [
            // From orders table
            'total_amount' => $order->total_amount,
            'delivery_fee' => $order->delivery_fee,
            
            // From deliveries table
            'arrival_time' => $delivery?->arrival_time ?? '',
            'driver_id' => $delivery?->driver_id ?? 0,
            'driver_route' => $delivery?->driver_route ?? '',
            'backup_driver_id' => $delivery?->backup_driver_id ?? null,
            'driver_notes' => $delivery?->driver_notes ?? '',
        ];
    }

    /**
     * Store delivery data from form input (driver-related fields only)
     */
    public function storeDeliveryData(Order $order, array $deliveryData): Delivery
    {
        // Filter to only driver-related fields
        $driverFields = array_intersect_key($deliveryData, array_flip([
            'arrival_time', 'driver_id', 'driver_route', 'backup_driver_id', 'driver_notes'
        ]));
        
        $delivery = $this->getOrderDelivery($order);
        
        if ($delivery) {
            $this->updateDelivery($delivery, $driverFields);
            return $delivery;
        }
        
        return Delivery::create(array_merge($driverFields, [
            'deliverable_type' => 'order',
            'deliverable_id' => $order->id,
            'customer_id' => $order->customer_id,
            'address_id' => $order->address_id,
            'delivery_date' => $order->delivery_date,
        ]));
    }
}