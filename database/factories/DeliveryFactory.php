<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'delivery_no' => \App\Models\Delivery::generateDeliveryNo(),
            'delivery_date' => fake()->dateTimeBetween('now', '+30 days'),
            'arrival_time' => fake()->randomElement(['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00']),
            'driver_id' => Driver::inRandomOrder()->first()?->id ?? Driver::factory(),
            'driver_route' => fake()->randomElement(['Route 1', 'Route 2', 'Route 3']),
            'backup_driver_id' => fake()->optional(0.3)->randomElement(Driver::pluck('id')->toArray()),
            'driver_notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
