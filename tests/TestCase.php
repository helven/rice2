<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed reference data once for all tests
        $this->seedReferenceData();
    }
    
    protected function seedReferenceData(): void
    {
        // Seed common reference data that ALL tests will use
        \DB::table('order_statuses')->insert(['id' => 1, 'label' => 'Pending']);
        \DB::table('attr_payment_methods')->insert(['id' => 1, 'name' => 'Cash']);
        \App\Models\Customer::create(['id' => 1, 'name' => 'Test Customer', 'phone' => '123']);
        \App\Models\CustomerAddressBook::create(['id' => 1, 'customer_id' => 1, 'address' => 'Test', 'city' => 'Test', 'state' => 'Test', 'postcode' => '12345']);
        \App\Models\Meal::create(['id' => 1, 'name' => 'Test Meal', 'price' => 10]);
        \App\Models\Driver::create(['id' => 1, 'name' => 'Test Driver', 'phone' => '123']);
    }
}
