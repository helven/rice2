<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttrPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attr_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('label')->default('')->nullable();;
            $table->string('name')->default('')->nullable();;
            $table->string('address_1')->default('')->nullable();
            $table->string('address_2')->default('')->nullable();
            $table->string('postal_code')->default('')->nullable();
            $table->string('city')->default('')->nullable();
            $table->text('payment_term')->nullable();
            $table->integer('state_id')->default(0);
            $table->integer('country_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attr_payment_methods');
    }
}
