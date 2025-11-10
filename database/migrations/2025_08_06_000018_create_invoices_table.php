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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('status_id')->default(1);
            $table->string('invoice_no')->default('');
            $table->string('ref_no')->default('');
            $table->string('billing_name')->default('');
            $table->text('billing_address')->nullable();
            $table->string('tax_no')->default('');
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('status_id');
            $table->unique('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};