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
        Schema::create('erate_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('owner_id');
            $table->string('product_description');
            $table->string('phone_number');
            $table->date('establish_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('lockin_start_date')->nullable();
            $table->date('lockin_end_date')->nullable();
            $table->tinyInteger('lockin_length')->nullable();
            $table->tinyInteger('status_id');
            $table->string('sales_store')->nullable();
            $table->string('sales_rep')->nullable();
            $table->string('brand_id');
            $table->smallInteger('service_status');
            $table->string('imsi', 15)->nullable();
            $table->timestamp('port_date')->nullable();
            $table->timestamp('last_logged_in')->nullable();
            $table->string('platform')->nullable();
            $table->string('order_id')->nullable();
            $table->string('entered_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erate_subscriptions');
    }
};
