<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mall>
 */
class MallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Mall: '.fake()->company(),
            'status_id' => fake()->randomElement([1, 2]),
        ];
    }
}