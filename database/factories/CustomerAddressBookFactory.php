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
        $isMall = fake()->boolean(); // Randomly decide between mall and area
        $isMall = false;
        // Malaysian cities list
        $malaysianCities = [
            'Kuala Lumpur', 'George Town', 'Ipoh', 'Shah Alam', 'Subang Jaya',
            'Johor Bahru', 'Klang', 'Petaling Jaya', 'Kuantan', 'Alor Setar'
        ];

        return [
            'status_id' => fake()->randomElement([1]),
            'is_default' => false,
            'name' => ucwords(fake()->word()) . ' ' . fake()->companySuffix(),
            'contact' => '',
            'email' => '',
            'mall_id' => $isMall ? fake()->numberBetween(1, 5) : 0,
            'area_id' => !$isMall ? fake()->numberBetween(1, 5) : 0,
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->boolean(30) ? fake()->secondaryAddress() : '',
            'postal_code' => fake()->numerify('#####'), // Ensures 5-digit numeric postcode
            'city' => fake()->randomElement($malaysianCities), // Selects random Malaysian city
            'state_id' => fake()->numberBetween(1, 16), // Assuming states are numbered 1-16
            'country_id' => 1, // Assuming 1 is the default country code
            'driver_id' => fake()->numberBetween(1, 5),
            'driver_route' => fake()->randomElement(['Route 1', 'Route 2', 'Route 3']),
            'backup_driver_id' => fake()->numberBetween(1, 5), // 30% chance of having a backup driver
            'backup_driver_route' => fake()->randomElement([null, 'Route 1', 'Route 2', 'Route 3']),
        ];
    }
}