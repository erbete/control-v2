<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telavox_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('seller_id');
            $table->string('company_name')->nullable();
            $table->string('reg_num')->nullable();
            $table->string('street')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('created');
            $table->timestamp('inactivation_time')->nullable();
            $table->text('note')->nullable();
            $table->string('seller_name');
            $table->string('deal_num')->nullable();
            $table->tinyInteger('paytime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telavox_customers');
    }
};
