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
        Schema::create('customer_statuses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label')->default('');
            $table->string('description')->default('');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id')->default(1);
            $table->string('name')->default('');
            $table->string('contact')->default('');
            $table->integer('payment_method_id')->default(0);
            $table->timestamps();

            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customers_statuses');
    }
};