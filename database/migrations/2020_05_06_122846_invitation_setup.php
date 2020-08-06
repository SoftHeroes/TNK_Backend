<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvitationSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitationSetup', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->integer('maximumRequestInDay')->default(10)->nullable();
            $table->integer('requestMin')->default(60)->nullable();
            $table->integer('maximumRequestInMin')->default(3)->nullable();
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updateAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitationSetup');
    }
}
