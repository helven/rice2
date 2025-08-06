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
        Schema::create('meal_packages', function (Blueprint $table) {
            $table->id();
            $table->integer('meal_id');
            $table->string('name')->default('');
            $table->string('main_image')->default('')->nullable();
            $table->json('dish_images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_packages');
    }
};
