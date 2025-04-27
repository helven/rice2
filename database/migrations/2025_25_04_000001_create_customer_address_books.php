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
        Schema::create('customer_address_books', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('customer_id'); // Changed from foreignId
            $table->string('name')->default('');
            $table->string('contact')->default('');
            $table->string('email')->default('');
            $table->string('address_1')->default('');
            $table->string('address_2')->default('')->nullable();
            $table->string('postal_code')->default('');
            $table->string('city')->default('');
            $table->integer('state')->default(0);
            $table->integer('country')->default(0);
            $table->timestamps();

            // Index for faster queries
            $table->index('status');
            $table->index('customer_id');
            $table->index(['customer_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_address_books');
    }
};