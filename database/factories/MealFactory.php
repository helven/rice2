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
            $baseName = fake()->randomElement(['Nasi Lemak', 'Nasi Goreng', 'Mee Goreng', 'Char Kuey Teow', 'Roti Canai', 'Laksa', 'Rendang', 'Satay', 'Ayam Percik', 'Nasi Kerabu', 'Mee Rebus', 'Curry Mee', 'Nasi Dagang', 'Ikan Bakar', 'Ayam Penyet']);
            $variant = fake()->optional(0.3)->randomElement([' Spicy', ' Special', ' Deluxe', ' Original', ' Premium']);
            $meal->update([
                'name' => $baseName . ($variant ?? ''),
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
