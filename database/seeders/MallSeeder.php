<?php

namespace Database\Seeders;

use App\Models\Mall;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MallSeeder extends Seeder
{
    public function run(): void
    {
        // Insert mall statuses
        DB::table('mall_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'Mall is active.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('mall_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'Mall is inactive.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('mall_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'Mall is deleted.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mall::factory()
        //     ->count(5)
        //     ->create();
    }
}
