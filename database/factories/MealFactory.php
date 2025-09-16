<?php
// php artisan db:seed --class=MealSeeder

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{
    public function configure()
    {
        return $this->afterCreating(function ($meal) {
            $prefix = $meal->category_id == 1 ? '[Daily]' : '[Cater]';
            $meal->update([
                'name' => $prefix . '[' . $meal->id . '] ' . ucwords(fake()->words(1, true)),
            ]);
        });
    }

    public function definition(): array
    {
        return [
            'name' => 'Temporary Name', // Will be updated in configure()
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'status_id' => fake()->randomElement([1]),
            'category_id' => fake()->randomElement([1, 2]),
        ];
    }
}
