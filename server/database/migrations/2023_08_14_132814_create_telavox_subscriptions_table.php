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
        Schema::create('telavox_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('retailer_subscription_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('anum');
            $table->string('description');
            $table->string('mobile_anumber')->nullable();
            $table->string('fixed_anumber')->nullable();
            $table->timestamp('delivery_date');
            $table->timestamp('notify_cancel_time')->nullable();
            $table->timestamp('inactivation_time')->nullable();
            $table->timestamp('added_date')->nullable();
            $table->decimal('customer_month_cost')->default(0);
            $table->decimal('customer_one_time_cost')->default(0);
            $table->decimal('retailer_month_cost')->default(0);
            $table->decimal('retailer_one_time_cost')->default(0);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telavox_subscriptions');
    }
};
