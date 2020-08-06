<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StockHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockHistory', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('stockID')->unsigned();
            $table->double('stockValue'); //open value
            $table->date('createdDate');
            $table->time('createdTime');
            $table->foreign('stockID')->references('PID')->on('stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stockHistory');
    }
}
