<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MealSeeder extends Seeder
{
    public function run(): void
    {
        // Insert meal statuses
        DB::table('meal_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'Meal is active.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('meal_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'Meal is inactive.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('meal_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'Meal is deleted.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('meal_categories')->insert([
            'id' => 1,
            'label' => 'Daily Order',
            'description' => 'Daily Order.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('meal_categories')->insert([
            'id' => 2,
            'label' => 'Catering',
            'description' => 'Catering.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // $numberOfMeals = 15; // You can adjust this number
        // Meal::factory()->count($numberOfMeals)->create();
    }
}
