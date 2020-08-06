<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PoolLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poolLog', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('userID')->unsigned()->nullable();
            $table->bigInteger('adminID')->unsigned()->comment('This column admin ID which causes to update the balance in the system. But in case when system updates balance like to calculate game result etc., then this value will be zero (0)');;
            $table->double('previousBalance');
            $table->double('newBalance');
            $table->double('amount');
            $table->string('balanceType')->comment('This column holds update column name of Portal Provider table.');
            $table->tinyInteger('operation')->comment('0 = credit, 1 = debit , 2 = recharge');
            $table->bigInteger('transactionId')->unsigned();
            $table->string('UUID')->uniqid();
            $table->string('serviceName');
            $table->tinyInteger('source')->comment('This holds the value of the source from which update request come. But in case when system updates balance like to calculate game result etc., then this value will be zero (0)');
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
        Schema::dropIfExists('poolLog');
    }
}
