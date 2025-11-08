<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50);
            $table->string('value', 50)->unique();
            $table->timestamps();
        });

        DB::table('delivery_statuses')->insert([
            ['id' => 1, 'label' => 'Scheduled', 'value' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'label' => 'Delivered', 'value' => 'delivered', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'label' => 'Cancelled', 'value' => 'cancelled', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_statuses');
    }
};
