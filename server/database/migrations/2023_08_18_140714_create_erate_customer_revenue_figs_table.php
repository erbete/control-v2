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
        Schema::create('erate_customer_revenue_figs', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('period');
            $table->string('network');
            $table->string('service_status');
            $table->string('phone_number');
            $table->string('service_imsi')->nullable();
            $table->string('subscription_status');
            $table->timestamp('subscription_start_date');
            $table->timestamp('subscription_end_date')->nullable();
            $table->string('rate_plan_description');
            $table->string('u2_company')->nullable();
            $table->string('u2_first_name')->nullable();
            $table->string('u2_last_name')->nullable();
            $table->string('priceplan')->nullable();
            $table->boolean('priceplan_chg_last_mon');
            $table->decimal('total_revenue', 14, 4)->default(0);
            $table->decimal('traffic_revenue', 14, 4)->default(0);
            $table->decimal('subscription_fees_revenue', 14, 4)->default(0);
            $table->decimal('other_fees_revenue', 14, 4)->default(0);
            $table->decimal('total_traffic_cost')->default(0);
            $table->decimal('traffic_cost_inside_bundle')->default(0);
            $table->decimal('traffic_cost_outside_bundle')->default(0);
            $table->decimal('simcard_total_cost')->default(0);
            $table->decimal('mbb_total_cost')->default(0);
            $table->decimal('datacard_total_cost')->default(0);
            $table->decimal('twin_total_cost')->default(0);
            $table->unsignedInteger('sms_national_count')->default(0);
            $table->unsignedInteger('voice_national_seconds')->default(0);
            $table->unsignedBigInteger('total_bytes_national')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erate_customer_revenue_figs');
    }
};
