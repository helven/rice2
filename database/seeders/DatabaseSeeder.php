<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@ricereport.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin321'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('attr_status')->insert([
            'key'   => 'active',
            'label' => 'Active'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'inactive',
            'label' => 'Inactive'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'pending',
            'label' => 'Pending'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'banned',
            'label' => 'Banned'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'frozen',
            'label' => 'Frozen'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'expired',
            'label' => 'Expired'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'hidden',
            'label' => 'Hidden'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'published',
            'label' => 'Published'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'unpublished',
            'label' => 'Unublished'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'closed',
            'label' => 'Closed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'deleted',
            'label' => 'Deleted'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'unpaid',
            'label' => 'Unpaid'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'paid',
            'label' => 'Paid'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'completed',
            'label' => 'Completed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'Waiting Approval',
            'label' => 'Waiting Approval'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'approved',
            'label' => 'Approved'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'rejected',
            'label' => 'Rejected'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'canceled',
            'label' => 'Canceled'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'redeemed',
            'label' => 'Redeemed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'dispute',
            'label' => 'Dispute'
        ]);

        \DB::table('attr_state')->insert([
            'key'   => 'johor',
            'label' => 'Johor'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kedah',
            'label' => 'Kedah'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kelantan',
            'label' => 'Kelantan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kuala_lumpur',
            'label' => 'Kuala Lumpur'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'labuan',
            'label' => 'Labuan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'melaka',
            'label' => 'Melaka'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'negeri_sembilan',
            'label' => 'Negeri Sembilan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'pahang',
            'label' => 'Pahang'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'perak',
            'label' => 'Perak'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'perlis',
            'label' => 'Perlis'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'pulau_pinang',
            'label' => 'Pulau Pinang'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'putra_jaya',
            'label' => 'Putrajaya'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'sabah',
            'label' => 'Sabah'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'sarawak',
            'label' => 'Sarawak'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'selangor',
            'label' => 'Selangor'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'terengganu',
            'label' => 'Terengganu'
        ]);

        \DB::table('attr_payment_method')->insert([
            'key'   => 'bt',
            'label' => 'BT'
        ]);
        \DB::table('attr_payment_method')->insert([
            'key'   => 'mom',
            'label' => 'MOM'
        ]);
        \DB::table('attr_payment_method')->insert([
            'key'   => 'my',
            'label' => 'MY'
        ]);
        \DB::table('attr_payment_method')->insert([
            'key'   => 'un',
            'label' => 'UN'
        ]);
    }
}
