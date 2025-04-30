<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    public function run(): void
    {
        $numberOfMeals = 10; // You can adjust this number
        Meal::factory()->count($numberOfMeals)->create();
    }
}
