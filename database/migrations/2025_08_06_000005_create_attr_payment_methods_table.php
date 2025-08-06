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
            $table->string('label')->default('');
            $table->string('name')->default('');
            $table->string('contact')->default('');
            $table->string('address_1')->default('');
            $table->string('address_2')->default('');
            $table->string('postal_code')->default('');
            $table->string('city')->default('');
            $table->text('payment_terms')->nullable();
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
