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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->string('name')->default('32');
            $table->string('contact')->default('32');
            $table->string('ic_name')->default('32');
            $table->string('ic_no')->default('32');
            $table->text('address')->nullable();
            $table->text('route')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
