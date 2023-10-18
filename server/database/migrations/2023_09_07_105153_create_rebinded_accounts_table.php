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
        Schema::create('rebinded_accounts', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamp('rebinded_at')->useCurrent();
            $table->foreignId('account_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rebinded_accounts');
    }
};
