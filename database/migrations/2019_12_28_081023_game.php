<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Game extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('providerGameSetupID')->unsigned();
            $table->bigInteger('stockID')->unsigned();
            $table->date('startDate');
            $table->time('startTime');
            $table->date('endDate');
            $table->time('endTime');
            $table->dateTime('betCloseTime');
            $table->tinyInteger('gameStatus')->default(0)->comment('0 = pending , 1 = open , 2 = close , 3 = complete, 4 = error , 5 = Deleted');
            $table->double('endStockValue')->nullable();
            $table->string('UUID')->unique();
            $table->date('createdDate');
            $table->time('createdTime');
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->string('error','5000')->nullable();
            $table->foreign('providerGameSetupID')->references('PID')->on('providerGameSetup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game');
    }
}
