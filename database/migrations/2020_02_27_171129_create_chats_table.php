<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('userID')->unsigned();
            $table->bigInteger('gameID')->unsigned()->nullable();
            $table->enum('chatType', ['1','2'])->comment('1=Game,2=Global');
            $table->string('message');
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat');
    }
}
