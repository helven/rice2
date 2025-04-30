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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('address_id');
            $table->integer('status_id')->default(1);
            $table->datetime('delivery_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedInteger('driver_id')->default(0);
            $table->string('driver_route')->default('');
            $table->unsignedInteger('backup_driver_id')->default(0);
            $table->string('backup_driver_route')->default('');
            $table->timestamps();

            $table->index('status_id');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};