<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class TestDriverSeeder extends Seeder
{
    public function run(): void
    {
        $numberOfDrivers = 5; // You can adjust this number
        Driver::factory()->count($numberOfDrivers)->create();
    }
}
