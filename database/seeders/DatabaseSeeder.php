<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Insert user statuses first
        DB::table('user_statuses')->insert([
            'id' => 1,
            'label' => 'Active',
            'description' => 'User is active and can log in.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_statuses')->insert([
            'id' => 2,
            'label' => 'Inactive',
            'description' => 'User is inactive and cannot log in.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_statuses')->insert([
            'id' => 99,
            'label' => 'Deleted',
            'description' => 'User is deleted and cannot log in.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Then create the admin user with a valid status_id
        User::factory()->create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@ricereport.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin321'),
            'status_id' => 1, // Set to Active status
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('attr_statuses')->insert([
            'key'   => 'active',
            'label' => 'Active'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'inactive',
            'label' => 'Inactive'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'pending',
            'label' => 'Pending'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'banned',
            'label' => 'Banned'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'frozen',
            'label' => 'Frozen'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'expired',
            'label' => 'Expired'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'hidden',
            'label' => 'Hidden'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'published',
            'label' => 'Published'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'unpublished',
            'label' => 'Unublished'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'closed',
            'label' => 'Closed'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'deleted',
            'label' => 'Deleted'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'unpaid',
            'label' => 'Unpaid'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'paid',
            'label' => 'Paid'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'completed',
            'label' => 'Completed'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'Waiting Approval',
            'label' => 'Waiting Approval'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'approved',
            'label' => 'Approved'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'rejected',
            'label' => 'Rejected'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'canceled',
            'label' => 'Canceled'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'redeemed',
            'label' => 'Redeemed'
        ]);
        DB::table('attr_statuses')->insert([
            'key'   => 'dispute',
            'label' => 'Dispute'
        ]);

        DB::table('attr_states')->insert([
            'key'   => 'johor',
            'label' => 'Johor'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'kedah',
            'label' => 'Kedah'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'kelantan',
            'label' => 'Kelantan'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'kuala_lumpur',
            'label' => 'Kuala Lumpur'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'labuan',
            'label' => 'Labuan'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'melaka',
            'label' => 'Melaka'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'negeri_sembilan',
            'label' => 'Negeri Sembilan'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'pahang',
            'label' => 'Pahang'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'perak',
            'label' => 'Perak'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'perlis',
            'label' => 'Perlis'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'pulau_pinang',
            'label' => 'Pulau Pinang'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'putra_jaya',
            'label' => 'Putrajaya'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'sabah',
            'label' => 'Sabah'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'sarawak',
            'label' => 'Sarawak'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'selangor',
            'label' => 'Selangor'
        ]);
        DB::table('attr_states')->insert([
            'key'   => 'terengganu',
            'label' => 'Terengganu'
        ]);

        DB::table('attr_payment_methods')->insert([
            'key'   => 'bt',
            'label' => 'BT',
            'name'=> 'My Bento Food And Beverage'
        ]);
        DB::table('attr_payment_methods')->insert([
            'key'   => 'mom',
            'label' => 'MOM',
            'name'=> 'Moms Recipe Caterin'
        ]);
        DB::table('attr_payment_methods')->insert([
            'key'   => 'my',
            'label' => 'MY',
            'name'=> 'My Home Economy Rice'
        ]);
        DB::table('attr_payment_methods')->insert([
            'key'   => 'un',
            'label' => 'UN',
            'name'=> 'United Economy Rice Food & Beverage'
        ]);

        // Call other seeders
        $this->call([
            AreaSeeder::class,
            DriverSeeder::class,
            CustomerSeeder::class,
            MallSeeder::class,
            MealSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
