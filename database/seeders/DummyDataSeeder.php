<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Mall;
use App\Models\Meal;
use App\Models\Area;
use App\Models\Order;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    // Insert Dummy Data
    // php artisan db:seed --class=DummyDataSeeder

    // Remove Dummy Data
    // php artisan tinker --execute="DB::table('order_meals')->truncate(); DB::table('deliveries')->truncate(); DB::table('invoices')->truncate(); DB::table('orders')->truncate(); DB::table('customer_address_books')->truncate(); DB::table('customers')->truncate(); DB::table('drivers')->truncate(); DB::table('malls')->truncate(); DB::table('meals')->truncate(); DB::table('areas')->truncate();"

    public function run(): void
    {
        // Master data factories
        Customer::factory()->count(5)->create();
        Driver::factory()->count(5)->create();
        Mall::factory()->count(5)->create();
        Meal::factory()->count(15)->create();
        Area::factory()->count(10)->create();
        
        // Order factories
        // 150 meal plans (3-7 deliveries each)
        Order::factory()->count(150)->mealPlan()->create();
        
        // 80 single orders (1 delivery each)
        Order::factory()->count(80)->single()->create();
        
        // Mix of payment statuses
        // 40 paid meal plans
        Order::factory()->count(40)->mealPlan()->paid()->create();
        
        // 30 unpaid single orders
        Order::factory()->count(30)->single()->unpaid()->create();
    }
}
