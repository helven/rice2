<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Insert customer statuses
        DB::table('customer_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'Customer is active.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('customer_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'Customer is inactive.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('customer_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'Customer is deleted.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::factory()
            ->count(3)  // This will create 10 customers, each with 1-3 addresses
            ->create();
    }
}
