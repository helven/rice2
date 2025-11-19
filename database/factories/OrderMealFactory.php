<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderMealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'normal' => fake()->numberBetween(0, 10),
            'big' => fake()->numberBetween(0, 5),
            'small' => fake()->numberBetween(0, 5),
            'no_rice' => fake()->numberBetween(0, 3),
        ];
    }
}
