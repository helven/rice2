<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Area: '.fake()->city(),
            'status_id' => fake()->randomElement([1, 2]),
            'postal' => fake()->postcode(),
            'delivery_fee' => [
                ['qty' => 1, 'delivery_fee' => fake()->numberBetween(4, 6)],
                ['qty' => 2, 'delivery_fee' => fake()->numberBetween(3, 5)],
                ['qty' => 3, 'delivery_fee' => fake()->numberBetween(2, 4)],
                ['qty' => 4, 'delivery_fee' => fake()->numberBetween(1, 3)],
                ['qty' => 5, 'delivery_fee' => 0]
            ],
        ];
    }
}