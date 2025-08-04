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
        Schema::create('driver_statuses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label')->default('');
            $table->string('description')->default('');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id')->default(1);
            $table->string('name')->default('');
            $table->string('contact')->default('');
            $table->string('ic_name')->default('');
            $table->string('ic_no')->default('');
            $table->text('address')->nullable();
            $table->text('route')->nullable();
            $table->timestamps();

            $table->index('status_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('driver_statuses');
    }
};
