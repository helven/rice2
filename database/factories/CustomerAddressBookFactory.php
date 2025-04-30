<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerAddressBook>
 */
class CustomerAddressBookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status_id' => fake()->randomElement([1]),
            'is_default' => false,
            'name' => fake()->company(),
            'contact' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->optional(0.3)->secondaryAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'state_id' => fake()->numberBetween(1, 16), // Assuming states are numbered 1-16
            'country_id' => 1, // Assuming 1 is the default country code
        ];
    }
}