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
            'name' => fake()->name(),
            'contact' => fake()->phoneNumber(),
            'status_id' => fake()->randomElement([1, 2]),
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