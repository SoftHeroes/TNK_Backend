<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiActivityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apiActivityLog', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('service');
            $table->string('method');
            $table->integer('responseCode');
            $table->string('responseMessage');
            $table->boolean('errorFound');
            $table->dateTime('requestTime');
            $table->dateTime('responseTime');
            $table->integer('timeTaken');
            $table->text('request');
            $table->text('response');
            $table->bigInteger('portalProviderID')->unsigned()->nullable();
            $table->bigInteger('adminID')->unsigned()->nullable();
            $table->bigInteger('userID')->unsigned()->nullable();
            $table->string('version');
            $table->string('source');
            $table->string('ipAddress');
            $table->boolean('exceptionFound');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apiActivityLog');
    }
}
