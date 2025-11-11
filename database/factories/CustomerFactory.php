<?php
// php artisan db:seed --class=CustomerSeeder

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Ahmad', 'Ali', 'Tan', 'Lee', 'Kumar', 'Siti', 'Nurul', 'Wong', 'Lim', 'Raj']) . ' ' . fake()->randomElement(['Abdullah', 'Hassan', 'Wei', 'Ying', 'Muthu', 'Aziz', 'Chong', 'Singh', 'Binti', 'Bin']),
            'contact' => '01' . fake()->randomElement(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']) . '-' . fake()->numerify('### ####'),
            'status_id' => fake()->randomElement([1]),
            'payment_method_id' => fake()->randomElement([1, 2, 3, 4]),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Customer $customer) {
            \App\Models\CustomerAddressBook::factory()->create([
                'customer_id' => $customer->id,
                'is_default' => true,
            ]);

            // Randomly create 0-2 additional addresses
            $additionalAddresses = fake()->numberBetween(0, 2);
            for ($i = 0; $i < $additionalAddresses; $i++) {
                \App\Models\CustomerAddressBook::factory()->create([
                    'customer_id' => $customer->id,
                    'is_default' => false,
                ]);
            }
        });
    }
}