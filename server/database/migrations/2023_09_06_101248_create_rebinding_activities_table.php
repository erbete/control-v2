<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Control\Rebinding\RebindingStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rebinding_activities', function (Blueprint $table) {
            $table->id();
            $table->enum('status', array_column(RebindingStatus::cases(), 'value'))->default('NONE');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('account_id')
                ->unique()
                ->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rebinding_activities');
    }
};
