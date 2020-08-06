<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditRequest', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->double('amount');
            $table->tinyInteger('requestStatus')->default(0)->comment('0 = pending,1 = approved,2 = cancel');
            $table->text('creditRequestDescription')->nullable();
            $table->string('creditRequestImage');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('currencyID')->unsigned();
            $table->bigInteger('adminID')->unsigned()->nullable();
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            $table->foreign('currencyID')->references('PID')->on('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creditRequest');
    }
}
