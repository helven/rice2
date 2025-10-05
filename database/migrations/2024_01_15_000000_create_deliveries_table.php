<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_no', 20)->unique();

            // Polymorphic relationship
            $table->enum('deliverable_type', ['order', 'meal_plan']);
            $table->unsignedBigInteger('deliverable_id');

            // Delivery details
            $table->unsignedInteger('address_id')->default(0);
            $table->date('delivery_date')->nullable();
            $table->time('arrival_time')->default('00:00:00');
            $table->string('dropoff_time', 8)->default('');

            // Driver info
            $table->unsignedInteger('driver_id')->default(0);
            $table->string('driver_route')->default('');
            $table->unsignedInteger('backup_driver_id')->nullable();
            $table->string('backup_driver_route')->default('')->nullable();
            $table->text('driver_notes')->nullable();

            // Status tracking
            $table->json('delivery_proof')->nullable(); // photos, signatures, etc.

            $table->timestamps();

            // Indexes
            $table->index(['deliverable_type', 'deliverable_id'], 'idx_deliverable');
            $table->index('delivery_date', 'idx_delivery_date');
            $table->index('driver_id', 'idx_driver');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};