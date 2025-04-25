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
            'value' => 'Active'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'inactive',
            'value' => 'Inactive'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'pending',
            'value' => 'Pending'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'banned',
            'value' => 'Banned'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'frozen',
            'value' => 'Frozen'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'expired',
            'value' => 'Expired'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'hidden',
            'value' => 'Hidden'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'published',
            'value' => 'Published'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'unpublished',
            'value' => 'Unublished'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'closed',
            'value' => 'Closed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'deleted',
            'value' => 'Deleted'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'unpaid',
            'value' => 'Unpaid'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'paid',
            'value' => 'Paid'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'completed',
            'value' => 'Completed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'Waiting Approval',
            'value' => 'Waiting Approval'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'approved',
            'value' => 'Approved'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'rejected',
            'value' => 'Rejected'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'canceled',
            'value' => 'Canceled'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'redeemed',
            'value' => 'Redeemed'
        ]);
        \DB::table('attr_status')->insert([
            'key'   => 'dispute',
            'value' => 'Dispute'
        ]);

        \DB::table('attr_state')->insert([
            'key'   => 'johor',
            'value' => 'Johor'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kedah',
            'value' => 'Kedah'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kelantan',
            'value' => 'Kelantan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'kuala_lumpur',
            'value' => 'Kuala Lumpur'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'labuan',
            'value' => 'Labuan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'melaka',
            'value' => 'Melaka'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'negeri_sembilan',
            'value' => 'Negeri Sembilan'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'pahang',
            'value' => 'Pahang'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'perak',
            'value' => 'Perak'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'perlis',
            'value' => 'Perlis'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'pulau_pinang',
            'value' => 'Pulau Pinang'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'putra_jaya',
            'value' => 'Putrajaya'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'sabah',
            'value' => 'Sabah'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'sarawak',
            'value' => 'Sarawak'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'selangor',
            'value' => 'Selangor'
        ]);
        \DB::table('attr_state')->insert([
            'key'   => 'terengganu',
            'value' => 'Terengganu'
        ]);
    }
}
