<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()
            ->count(10)  // This will create 10 customers, each with 1-3 addresses
            ->create();
    }
}