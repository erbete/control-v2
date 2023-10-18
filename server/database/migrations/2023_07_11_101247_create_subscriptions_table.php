<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Control\Infrastructure\SubscriptionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('phone_number');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('establish_date'); // Telavox: added_date
            $table->date('delivery_date'); // Erate: start_date
            // $table->date('lockin_start_date')->nullable(); // Telavox:
            $table->date('lockin_end_date')->nullable(); // Telavox: inactivation_time
            // $table->tinyInteger('lockin_length')->nullable();
            $table->string('description');
            $table->enum('status', SubscriptionStatus::names());
            // $table->enum('platform', [
            //     'ERATE',
            // ]);
            $table->string('imsi')->nullable();
            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
