<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderDate = fake()->dateTimeBetween('-30 days', '+30 days');
        
        return [
            'order_no' => '', // Will be set after creation
            'order_type' => fake()->randomElement(['meal_plan', 'single']),
            'order_date' => $orderDate,
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'payment_status_id' => fake()->randomElement([3, 4]), // active, unpaid, paid
            'payment_method_id' => fake()->randomElement([1, 2, 3, 4]),
            'total_amount' => fake()->randomFloat(2, 50, 500),
            'delivery_fee' => fake()->randomElement([0, 5, 10, 15]),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Order $order) {
            Order::unsetEventDispatcher();
            \App\Models\Delivery::unsetEventDispatcher();
        })->afterCreating(function (Order $order) {
            $customer = $order->customer;
            $address = $customer->addressBooks()->where('is_default', true)->first() 
                ?? $customer->addressBooks()->first();

            if (!$address) {
                $address = \App\Models\CustomerAddressBook::factory()->create([
                    'customer_id' => $customer->id,
                    'is_default' => true,
                ]);
            }

            // Create deliveries based on order type
            if ($order->order_type === 'meal_plan') {
                $this->createMealPlanDeliveries($order, $address);
            } else {
                $this->createSingleDelivery($order, $address);
            }

            // Create meals
            $this->createMeals($order);

            // Generate and set order number
            $firstDelivery = $order->deliveries()->first();
            $orderNo = Order::generateOrderNumber(
                $order->id,
                $address->mall_id ?? null,
                $firstDelivery?->delivery_date
            );
            $order->update(['order_no' => $orderNo]);
            
            // Manually trigger invoice creation after deliveries exist
            $dispatcher = new \Illuminate\Events\Dispatcher(app());
            Order::setEventDispatcher($dispatcher);
            \App\Models\Delivery::setEventDispatcher($dispatcher);
            app(\App\Services\InvoiceService::class)->handleOrderSaved($order);
        });
    }

    private function createMealPlanDeliveries($order, $address)
    {
        $days = fake()->numberBetween(3, 7);
        $orderDate = is_string($order->order_date) ? $order->order_date : $order->order_date->format('Y-m-d');
        $startDate = date('Y-m-d', strtotime($orderDate . ' +' . fake()->numberBetween(0, 3) . ' days'));
        
        for ($i = 0; $i < $days; $i++) {
            \App\Models\Delivery::factory()->create([
                'deliverable_id' => $order->id,
                'delivery_date' => date('Y-m-d', strtotime($startDate . " +$i days")),
                'address_id' => $address->id,
            ]);
        }
    }

    private function createSingleDelivery($order, $address)
    {
        $orderDate = is_string($order->order_date) ? $order->order_date : $order->order_date->format('Y-m-d');
        $deliveryDate = date('Y-m-d', strtotime($orderDate . ' +' . fake()->numberBetween(0, 7) . ' days'));
        \App\Models\Delivery::factory()->create([
            'deliverable_id' => $order->id,
            'delivery_date' => $deliveryDate,
            'address_id' => $address->id,
        ]);
    }

    private function createMeals($order)
    {
        $mealCount = fake()->numberBetween(1, 4);
        
        for ($i = 0; $i < $mealCount; $i++) {
            $meal = \App\Models\Meal::inRandomOrder()->first();
            
            if ($meal) {
                \App\Models\OrderMeal::factory()->create([
                    'order_id' => $order->id,
                    'meal_id' => $meal->id,
                ]);
            }
        }
    }

    // States for specific scenarios
    public function mealPlan()
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'meal_plan',
        ]);
    }

    public function single()
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'single',
        ]);
    }

    public function paid()
    {
        return $this->state(fn (array $attributes) => [
            'payment_status_id' => 4,
        ]);
    }

    public function unpaid()
    {
        return $this->state(fn (array $attributes) => [
            'payment_status_id' => 3,
        ]);
    }
}
