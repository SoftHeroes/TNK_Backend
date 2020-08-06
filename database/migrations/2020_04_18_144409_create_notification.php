<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('announcement');

        Schema::create('notification', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('UUID')->unique();
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('adminID')->unsigned()->nullable();
            $table->bigInteger('fromID')->unsigned()->nullable();
            $table->bigInteger('toID')->unsigned()->nullable();
            $table->tinyInteger('type')->default(0)->comment('0 = admin , 1 = follow , 2 = unFollow, 3 = balanceUpdate, 4 = welcome');
            $table->string('title');
            $table->string('message',1000);
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification');
    }
}
