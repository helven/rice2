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

        return [
            'name' => fake()->name(),
            'contact' => fake()->phoneNumber(),
            'ic_name' => fake()->name(),
            'ic_no' => fake()->unique()->numerify('######-##-####'),
            'address' => fake()->address(),
            'route' => $routes,
            'status' => fake()->randomElement([1, 2]),
        ];
    }
}
