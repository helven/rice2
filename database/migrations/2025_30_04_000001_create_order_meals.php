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
        Schema::create('order_meals', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id')->default(1);
            $table->unsignedInteger('order_id'); // Changed from foreignId
            $table->unsignedInteger('meal_id');
            $table->integer('normal_rice')->default(0);
            $table->integer('small_rice')->default(0);
            $table->integer('no_rice')->default(0);
            $table->integer('vegi')->default(0);
            $table->timestamps();

            // Index for faster queries
            $table->index('status_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_meals');
    }
};