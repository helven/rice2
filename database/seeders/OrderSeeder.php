<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Insert order statuses
        DB::table('order_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'Order is active.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'Order is inactive.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_statuses')->insert([
            'id' => 3,
            'label' => 'Unpaid',
            'description' => 'Order is unpaid.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_statuses')->insert([
            'id' => 4,
            'label' => 'Paid',
            'description' => 'Order is paid.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'Order is deleted.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


    }
}
