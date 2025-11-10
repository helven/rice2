<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Seed data
        DB::table('invoice_statuses')->insert([
            ['id' => 1, 'label' => 'Active', 'description' => 'Currently active invoice', 'is_system' => true],
            ['id' => 2, 'label' => 'Void', 'description' => 'Voided invoice (replaced by newer invoice)', 'is_system' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_statuses');
    }
};
