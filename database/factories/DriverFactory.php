<?php
// php artisan db:seed --class=DriverSeeder

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{

    public function definition(): array
    {
        $numRoutes = fake()->numberBetween(1, 3);
        $routes = [];
        
        for ($i = 0; $i < $numRoutes; $i++) {
            $routes[] = [
                'route_name' => 'Route ' . ($i + 1)
            ];
        }

        $name = fake()->randomElement(['Ahmad', 'Ali', 'Tan', 'Lee', 'Kumar', 'Siti', 'Nurul', 'Wong', 'Lim', 'Raj']) . ' ' . fake()->randomElement(['Abdullah', 'Hassan', 'Wei', 'Ying', 'Muthu', 'Aziz', 'Chong', 'Singh', 'Binti', 'Bin']) . ' ' . fake()->lastName();

        return [
            'name' => $name,
            'contact' => '01' . fake()->randomElement(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']) . '-' . fake()->numerify('### ####'),
            'ic_name' => $name,
            'ic_no' => fake()->unique()->numerify('######-##-####'),
            'address' => fake()->numerify('##') . ', Jalan ' . fake()->lastName() . ' ' . fake()->numerify('#/#'),
            'route' => $routes,
            'status_id' => fake()->randomElement([1, 2]),
        ];
    }
}
