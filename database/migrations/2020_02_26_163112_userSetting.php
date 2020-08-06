<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userSetting', function (Blueprint $table) {
            $table->bigIncrements("PID");
            $table->bigInteger("userID")->unsigned();
            $table->tinyInteger("isAllowToVisitProfile")->default(1);
            $table->tinyInteger("isAllowToFollow")->default(1);
            $table->tinyInteger("isAllowToDirectMessage")->default(1);
            $table->tinyInteger("isSound")->default(1);
            $table->tinyInteger("isAllowToLocation")->default(1);
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('userID')->references('PID')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userSetting');
    }
}
