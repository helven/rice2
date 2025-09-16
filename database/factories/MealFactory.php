<?php
// php artisan db:seed --class=MealSeeder

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => 'Meal: '.fake()->words(2, true),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'status_id' => fake()->randomElement([1]),
            'category_id' => fake()->randomElement([1, 2]),
        ];
    }
}
