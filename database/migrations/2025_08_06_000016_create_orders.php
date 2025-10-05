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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label')->default('');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->default('');
            $table->unsignedInteger('customer_id')->default(0);
            //$table->unsignedInteger('address_id')->default(0);
            $table->integer('status_id')->default(1);
            $table->integer('payment_status_id')->default(3);
            $table->integer('payment_method_id')->default(0);
            //$table->date('delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->text('notes')->nullable();
            //$table->time('arrival_time')->default('00:00:00');
            //$table->string('dropoff_time', 8)->default('');
            //$table->unsignedInteger('driver_id')->default(0);
            //$table->string('driver_route')->default('');
            //$table->unsignedInteger('backup_driver_id')->nullable();
            //$table->string('backup_driver_route')->default('')->nullable();
            //$table->text('driver_notes')->nullable();
            $table->timestamps();

            $table->index('status_id');
            //$table->index('delivery_date');
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
