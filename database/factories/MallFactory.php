<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mall>
 */
class MallFactory extends Factory
{
    private static $malls = ['Pavilion', 'Suria KLCC', 'Mid Valley Megamall', 'The Gardens Mall', 'Sunway Pyramid', 'IOI City', 'Berjaya Times Square', '1 Utama Shopping Centre', 'Queensbay Mall', 'Gurney Plaza', 'Paradigm Mall', 'Tropicana City Mall', 'Aeon Mall', 'MyTown Shopping Centre', 'The Curve', 'eCurve', 'Setia City Mall', 'Empire Shopping Gallery', 'Fahrenheit88', 'Lot 10'];
    private static $index = 0;

    public function definition(): array
    {
        return [
            'name' => self::$malls[self::$index++ % count(self::$malls)],
            'status_id' => fake()->randomElement([1, 2]),
        ];
    }
}