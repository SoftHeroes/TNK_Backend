<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Betting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('betting', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('gameID')->unsigned();
            $table->bigInteger('userID')->unsigned();
            $table->bigInteger('ruleID')->unsigned();
            $table->double('betAmount');
            $table->double('rollingAmount')->nullable();
            $table->double('payout');
            $table->tinyInteger('betResult')->default(-1)->comment('-1 = pending , 0 = lose , 1 = win');
            $table->tinyInteger('isBot')->default(0)->comment('0 = actual user , 1 = bot bet, 2 = bot type two');
            $table->string('source');
            $table->string('UUID')->unique();
            $table->date('createdDate');
            $table->time('createdTime');
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->foreign('gameID')->references('PID')->on('game');
            $table->foreign('userID')->references('PID')->on('user');
            $table->foreign('ruleID')->references('PID')->on('rule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('betting');
    }
}
