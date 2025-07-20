<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mall_statuses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('malls', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id')->default(1);
            $table->string('name')->default('');
            $table->integer('payment_medthod_id')->default(0);
            $table->timestamps();

            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('malls');
    }
};
