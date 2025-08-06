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
            $table->string('invoice_no')->default('')->index();
            $table->string('ref_no')->default('');
            $table->string('billing_name')->default('');
            $table->text('billing_address')->nullable();
            $table->string('tax_no')->default('');
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            
            // Add unique constraint on order_id
            $table->unique('order_id');
            
            // Add foreign key constraint
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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