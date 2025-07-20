<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        // Insert driver statuses
        DB::table('driver_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'Driver is active.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('driver_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'Driver is inactive.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('driver_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'Driver is deleted.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $numberOfDrivers = 5; // You can adjust this number
        Driver::factory()->count($numberOfDrivers)->create();
    }
}
