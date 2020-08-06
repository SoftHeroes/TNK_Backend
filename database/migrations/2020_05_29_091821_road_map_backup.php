<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RoadMapBackup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('roadMapBackup')) {
            Schema::create('roadMapBackup', function (Blueprint $table) {
                $table->bigIncrements('PID');
                $table->bigInteger('stockId')->unsigned();
                $table->longText('roadMap')->nullable();
                $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->foreign('stockId')->references('PID')->on('stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roadMapBackup');
    }
}
