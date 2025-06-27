<?php

namespace Database\Seeders;

use App\Models\Mall;
use Illuminate\Database\Seeder;

class MallSeeder extends Seeder
{
    public function run(): void
    {
        Mall::factory()
            ->count(5)
            ->create();
    }
}