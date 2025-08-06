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
            $table->unsignedInteger('order_id')->default(0);
            $table->unsignedInteger('meal_id')->default(0);
            $table->unsignedInteger('normal')->default(0);
            $table->unsignedInteger('big')->default(0);
            $table->unsignedInteger('small')->default(0);
            $table->unsignedInteger('s_small')->default(0);
            $table->unsignedInteger('no_rice')->default(0);
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