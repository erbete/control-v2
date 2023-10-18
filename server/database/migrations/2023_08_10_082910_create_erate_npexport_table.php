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
        Schema::create('erate_npexport', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('subscription_id');
            $table->string('phone_number');
            $table->timestamp('order_receive_date')->nullable();
            $table->timestamp('export_date')->nullable();
            $table->timestamp('exact_export_time')->nullable();
            $table->string('customer_name');
            $table->string('customer_id')->nullable();
            $table->unsignedInteger('operator_code')->nullable();
            $table->string('operator_descr')->nullable();
            $table->unsignedInteger('case_status_id');
            $table->unsignedInteger('status_id')->nullable();
            $table->string('status');
            $table->unsignedInteger('port_type_id')->nullable();
            $table->string('case_number')->nullable();
            $table->unsignedInteger('reject_code')->nullable();
            $table->string('reject_comment')->nullable();
            $table->enum('platform', ['SPNortel', 'SPAtea']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erate_npexport');
    }
};
