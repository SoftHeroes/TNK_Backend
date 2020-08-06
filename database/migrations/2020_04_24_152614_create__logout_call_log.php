<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogoutCallLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('logoutCallLog');
        Schema::create('logoutCallLog', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('service');
            $table->bigInteger('portalProviderID')->unsigned()->nullable();
            $table->bigInteger('adminID')->unsigned()->nullable();
            $table->bigInteger('userID')->unsigned()->nullable();
            $table->integer('responseCode')->nullable();
            $table->boolean('exceptionFound');
            $table->boolean('errorFound');
            $table->longText('responseMessage');
            $table->text('request');
            $table->text('response');
            $table->string('source');
            $table->string('ipAddress')->nullable();
            $table->string('version');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logoutCallLog');
    }
}
