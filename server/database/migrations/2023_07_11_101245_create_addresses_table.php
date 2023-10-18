<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->foreignId('account_owner_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        DB::statement(
            'ALTER TABLE addresses ADD CONSTRAINT check_is_all_null CHECK (address IS NOT NULL OR city IS NOT NULL OR zip IS NOT NULL OR country IS NOT NULL)'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
