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
        Schema::create('meal_statuses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label')->default('');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('meal_categories', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label')->default('');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id')->default(1);
            $table->integer('category_id')->default(1);
            $table->string('name')->default('');
            $table->string('code')->default('');
            $table->timestamps();

            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_statuses');
        Schema::dropIfExists('meal_categories');
        Schema::dropIfExists('meals');
    }
};
