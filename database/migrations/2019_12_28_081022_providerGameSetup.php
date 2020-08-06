<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProviderGameSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providerGameSetup', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('stockID')->unsigned();
            $table->tinyInteger('payoutType')->comment('1 = standard ,2 = dynamic ');
            $table->bigInteger('FD_BigSmallGameID')->unsigned();
            $table->bigInteger('FD_EvenOddGameID')->unsigned();
            $table->bigInteger('FD_LowMiddleHighGameID')->unsigned();
            $table->bigInteger('FD_NumberGameID')->unsigned();
            $table->bigInteger('LD_BigSmallGameID')->unsigned();
            $table->bigInteger('LD_EvenOddGameID')->unsigned();
            $table->bigInteger('LD_LowMiddleHighGameID')->unsigned();
            $table->bigInteger('LD_NumberGameID')->unsigned();
            $table->bigInteger('TD_BigSmallTieGameID')->unsigned();
            $table->bigInteger('TD_EvenOddGameID')->unsigned();
            $table->bigInteger('TD_LowMiddleHighGameID')->unsigned();
            $table->bigInteger('TD_NumberGameID')->unsigned();
            $table->bigInteger('BD_BigSmallTieGameID')->unsigned();
            $table->bigInteger('BD_EvenOddGameID')->unsigned();
            $table->bigInteger('BD_LowMiddleHighGameID')->unsigned();
            $table->bigInteger('BD_NumberGameID')->unsigned();
            $table->enum('isActive', ['active', 'inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            $table->foreign('stockID')->references('PID')->on('stock');
            $table->foreign('FD_BigSmallGameID')->references('PID')->on('gameSetup');
            $table->foreign('FD_EvenOddGameID')->references('PID')->on('gameSetup');
            $table->foreign('FD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            $table->foreign('FD_NumberGameID')->references('PID')->on('gameSetup');
            $table->foreign('LD_BigSmallGameID')->references('PID')->on('gameSetup');
            $table->foreign('LD_EvenOddGameID')->references('PID')->on('gameSetup');
            $table->foreign('LD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            $table->foreign('LD_NumberGameID')->references('PID')->on('gameSetup');
            $table->foreign('TD_BigSmallTieGameID')->references('PID')->on('gameSetup');
            $table->foreign('TD_EvenOddGameID')->references('PID')->on('gameSetup');
            $table->foreign('TD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            $table->foreign('TD_NumberGameID')->references('PID')->on('gameSetup');
            $table->foreign('BD_BigSmallTieGameID')->references('PID')->on('gameSetup');
            $table->foreign('BD_EvenOddGameID')->references('PID')->on('gameSetup');
            $table->foreign('BD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            $table->foreign('BD_NumberGameID')->references('PID')->on('gameSetup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providerGameSetup');
    }
}
