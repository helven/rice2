<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\CustomerAddressBook;

class DeliveryService
{


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
     * Get combined delivery data (orders + deliveries)
     */
    public function getCombinedDeliveryData(Order $order): array
    {
        $delivery = $order->getDelivery();
        
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
     * Store delivery data from form input
     */
    public function storeDeliveryData(Order $order, array $deliveryData): Delivery
    {
        // Filter to delivery-related fields including address_id
        $deliveryFields = array_intersect_key($deliveryData, array_flip([
            'delivery_date', 'arrival_time', 'driver_id', 'driver_route', 'backup_driver_id', 'driver_notes', 'address_id'
        ]));
        
        $delivery = $order->getDelivery();
        
        if ($delivery) {
            $delivery->update($deliveryFields);
            return $delivery;
        }
        
        return Delivery::create(array_merge($deliveryFields, [
            'deliverable_id' => $order->id,
        ]));
    }
}