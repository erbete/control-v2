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
        Schema::create('customer_expenditures', function (Blueprint $table) {
            $table->id();
            $table->jsonb('usage');
            $table->enum('source', [
                'ERATE',
                'CDR_ERATE',
                'TELAVOX',
                'CDR_TELAVOX'
            ]);
            $table->foreignId('subscription_id')
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
        Schema::dropIfExists('customer_expenditures');
    }
};
